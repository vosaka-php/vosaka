<?php
declare(strict_types=1);

namespace venndev\vosaka\runtime\eventloop;

use Generator;
use WeakMap;
use InvalidArgumentException;
use SplQueue;
use Throwable;
use venndev\vosaka\cleanup\GracefulShutdown;
use venndev\vosaka\io\JoinHandle;
use venndev\vosaka\runtime\eventloop\task\TaskPool;
use venndev\vosaka\runtime\eventloop\task\TaskState;
use venndev\vosaka\runtime\eventloop\task\Task;
use venndev\vosaka\core\MemoryManager;
use venndev\vosaka\time\Interval;
use venndev\vosaka\time\Sleep;
use venndev\vosaka\utils\CallableUtil;
use venndev\vosaka\utils\GeneratorUtil;
use venndev\vosaka\utils\MemUtil;
use venndev\vosaka\utils\Defer;
use venndev\vosaka\utils\sync\CancelFuture;

/**
 * Enhanced EventLoop class with task execution and core functionality.
 *
 * This class focuses on task execution and core event loop operations.
 */
final class EventLoop
{
    private TaskPool $taskPool;
    private SplQueue $runningTasks;
    private WeakMap $deferredTasks;
    private ?MemoryManager $memoryManager = null;
    private ?GracefulShutdown $gracefulShutdown = null;
    private bool $isRunning = false;

    // Memory monitoring
    private int $maxMemoryUsage;

    // Number of tasks handled per batch
    private int $batchSize = 40;

    // Control the number of iterations
    private int $iterationLimit = 1;
    private int $currentIteration = 0;
    private bool $enableIterationLimit = false;

    // Stream handler component
    private StreamHandler $streamHandler;

    public function __construct(int $maxMemoryMB = 128)
    {
        $this->taskPool = new TaskPool();
        $this->maxMemoryUsage = MemUtil::toKB($maxMemoryMB);
        $this->runningTasks = new SplQueue();
        $this->deferredTasks = new WeakMap();
        $this->streamHandler = new StreamHandler();
    }

    public function getMemoryManager(): MemoryManager
    {
        return $this->memoryManager ??= new MemoryManager(
            $this->maxMemoryUsage
        );
    }

    public function getGracefulShutdown(): GracefulShutdown
    {
        return $this->gracefulShutdown ??= new GracefulShutdown();
    }

    public function getStreamHandler(): StreamHandler
    {
        return $this->streamHandler;
    }

    /**
     * Add a read stream to the event loop
     */
    public function addReadStream($stream, callable $listener): void
    {
        $this->streamHandler->addReadStream($stream, $listener);
    }

    /**
     * Add a write stream to the event loop
     */
    public function addWriteStream($stream, callable $listener): void
    {
        $this->streamHandler->addWriteStream($stream, $listener);
    }

    /**
     * Remove a read stream from the event loop
     */
    public function removeReadStream($stream): void
    {
        $this->streamHandler->removeReadStream($stream);
    }

    /**
     * Remove a write stream from the event loop
     */
    public function removeWriteStream($stream): void
    {
        $this->streamHandler->removeWriteStream($stream);
    }

    /**
     * Add signal handler
     */
    public function addSignal(int $signal, callable $listener): void
    {
        $this->streamHandler->addSignal($signal, $listener);
    }

    /**
     * Remove signal handler
     */
    public function removeSignal(int $signal, callable $listener): void
    {
        $this->streamHandler->removeSignal($signal, $listener);
    }

    /**
     * Spawn method with fast path for common cases
     */
    public function spawn(callable|Generator $task, mixed $context = null): int
    {
        $taskObj = $this->taskPool->getTask(
            CallableUtil::makeAllToCallable($task),
            $context
        );

        $this->runningTasks->enqueue($taskObj);
        return $taskObj->id;
    }

    /**
     * Main run loop with stream support and batch processing
     */
    public function run(): void
    {
        $this->isRunning = true;

        while ($this->isRunning) {
            // Process tasks first
            $this->processRunningTasks();

            // Check iteration limits
            if ($this->isLimitedToIterations()) {
                break;
            }

            $timeout = $this->calculateSelectTimeout();
            $this->streamHandler->waitForStreamActivity($timeout);

            $this->memoryManager?->collectGarbage();

            if ($this->shouldStop()) {
                break;
            }
        }
    }

    /**
     * Process running tasks
     */
    private function processRunningTasks(): void
    {
        for (
            $i = 0;
            $i < $this->batchSize && !$this->runningTasks->isEmpty();
            $i++
        ) {
            $task = $this->runningTasks->dequeue();

            try {
                $this->executeTask($task);
            } catch (Throwable $e) {
                $this->failTask($task, $e);
            }
        }
    }

    /**
     * Calculate timeout for stream_select
     */
    private function calculateSelectTimeout(): ?int
    {
        // If we have pending tasks, don't block
        if (!$this->runningTasks->isEmpty()) {
            return 1;
        }

        // If we have streams or signals, wait indefinitely
        if (
            $this->streamHandler->hasStreams() ||
            $this->streamHandler->hasSignals()
        ) {
            return null;
        }

        // No activity expected, return immediately
        return 0;
    }

    /**
     * Check if event loop should stop
     */
    private function shouldStop(): bool
    {
        return $this->runningTasks->isEmpty() &&
            !$this->streamHandler->hasStreams() &&
            !$this->streamHandler->hasSignals();
    }

    /**
     * Task execution with reduced overhead
     */
    private function executeTask(Task $task): void
    {
        if ($task->state === TaskState::PENDING) {
            $task->state = TaskState::RUNNING;
            $task->callback = ($task->callback)($task->context, $this);
        }

        if ($task->state === TaskState::RUNNING) {
            $task->callback instanceof Generator
                ? $this->handleGenerator($task)
                : $this->completeTask($task, $task->callback);
        } elseif ($task->state === TaskState::SLEEPING) {
            $task->tryWake();
        }

        if (
            $task->state !== TaskState::COMPLETED &&
            $task->state !== TaskState::FAILED
        ) {
            $this->runningTasks->enqueue($task);
        }
    }

    /**
     * Generator handling with match expression
     */
    private function handleGenerator(Task $task): void
    {
        $generator = $task->callback;

        if (!$task->firstRun) {
            $task->firstRun = true;
        } else {
            $generator->next();
        }

        if (!$generator->valid()) {
            $result = GeneratorUtil::getReturnSafe($generator);
            $this->completeTask($task, $result);
            return;
        }

        $current = $generator->current();
        if ($current instanceof CancelFuture) {
            $this->completeTask(
                $task,
                GeneratorUtil::getReturnSafe($generator)
            );
        } elseif ($current instanceof Sleep || $current instanceof Interval) {
            $task->sleep($current->seconds);
        } elseif ($current instanceof Defer) {
            $this->addDeferredTask($task, $current);
        }
    }

    /**
     * Deferred task addition with pooling
     */
    private function addDeferredTask(Task $task, Defer $defer): void
    {
        if (!isset($this->deferredTasks[$task])) {
            $this->deferredTasks[$task] = [];
        }
        $this->deferredTasks[$task][] = $defer;
    }

    /**
     * Task completion with pooled arrays
     */
    private function completeTask(Task $task, mixed $result = null): void
    {
        $task->state = TaskState::COMPLETED;
        $this->taskPool->returnTask($task);

        if (isset($this->deferredTasks[$task])) {
            $deferredArray = $this->deferredTasks[$task];
            foreach ($deferredArray as $deferredTask) {
                ($deferredTask->callback)($result);
            }
            unset($this->deferredTasks[$task]);
        }

        JoinHandle::done($task->id, $result);
    }

    private function failTask(Task $task, Throwable $error): void
    {
        $task->state = TaskState::FAILED;
        $task->error = $error;
        $this->taskPool->returnTask($task);

        if (isset($this->deferredTasks[$task])) {
            unset($this->deferredTasks[$task]);
        }

        JoinHandle::done($task->id, $error);
    }

    public function stop(): void
    {
        $this->isRunning = false;
    }

    public function close(): void
    {
        $this->isRunning = false;
        $this->runningTasks = new SplQueue();
        $this->streamHandler->close();
    }

    // Iteration control methods
    public function setIterationLimit(int $limit): void
    {
        if ($limit <= 0) {
            throw new InvalidArgumentException(
                "Iteration limit must be positive"
            );
        }
        $this->enableIterationLimit = true;
        $this->iterationLimit = $limit;
    }

    public function resetIterationLimit(): void
    {
        $this->enableIterationLimit = false;
        $this->iterationLimit = 1;
        $this->currentIteration = 0;
    }

    public function resetIteration(): void
    {
        $this->currentIteration = 0;
    }

    public function canContinueIteration(): bool
    {
        if ($this->enableIterationLimit) {
            if ($this->currentIteration >= $this->iterationLimit) {
                return false;
            }
            $this->currentIteration++;
        }
        return true;
    }

    public function isLimitedToIterations(): bool
    {
        return $this->enableIterationLimit &&
            $this->currentIteration >= $this->iterationLimit;
    }

    public function setBatchSize(int $size): void
    {
        $this->batchSize = $size;
    }

    public function getStats(): array
    {
        return [
            "running_tasks" => $this->runningTasks->count(),
            "deferred_tasks" => $this->deferredTasks->count(),
            "stream_stats" => $this->streamHandler->getStats(),
            "task_pool_stats" => $this->taskPool->getStats(),
            "memory_usage" => memory_get_usage(true),
            "peak_memory" => memory_get_peak_usage(true),
        ];
    }
}
