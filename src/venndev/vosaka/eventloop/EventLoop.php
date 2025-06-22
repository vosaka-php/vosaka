<?php

declare(strict_types=1);

namespace venndev\vosaka\eventloop;

use Generator;
use InvalidArgumentException;
use RuntimeException;
use SplPriorityQueue;
use Throwable;
use venndev\vosaka\eventloop\scheduler\Defer;
use venndev\vosaka\eventloop\task\TaskPool;
use venndev\vosaka\eventloop\task\TaskState;
use venndev\vosaka\eventloop\task\Task;
use venndev\vosaka\core\MemoryManager;
use venndev\vosaka\time\Interval;
use venndev\vosaka\time\Sleep;
use venndev\vosaka\utils\CallableUtil;
use venndev\vosaka\utils\MemUtil;
use venndev\vosaka\utils\sync\CancelFuture;

final class EventLoop
{
    private SplPriorityQueue $readyQueue;
    private TaskPool $taskPool;
    private ?MemoryManager $memoryManager = null;
    private array $runningTasks = [];
    private array $chainedTasks = [];
    private array $deferredTasks = [];
    private bool $isRunning = false;

    // Memory monitoring
    private int $maxMemoryUsage;
    private int $taskProcessedCount = 0;
    private float $startTime;

    // Improved overload protection
    private int $maxTasksPerCycle = 10;
    private int $maxQueueSize = 5000;
    private float $maxExecutionTime = 0.1; // Max 100ms per cycle
    private int $currentCycleTaskCount = 0;
    private float $cycleStartTime = 0.0;

    // Backpressure handling
    private bool $enableBackpressure = true;
    private int $backpressureThreshold = 4000; // 80% of max queue
    private int $droppedTasks = 0;

    // Chain ID management
    private static int $nextChainId = 0;

    public function __construct(int $maxMemoryMB = 256)
    {
        $this->readyQueue = new SplPriorityQueue();
        $this->taskPool = new TaskPool();
        $this->maxMemoryUsage = MemUtil::toKB($maxMemoryMB);
    }

    public function getMemoryManager(): MemoryManager
    {
        if ($this->memoryManager === null) {
            $this->memoryManager = new MemoryManager($this->maxMemoryUsage);
        }
        return $this->memoryManager;
    }

    public function spawn(callable|Generator $task, mixed $context = null): int
    {
        // Check queue size limit
        if ($this->getQueueSize() >= $this->maxQueueSize) {
            if ($this->enableBackpressure) {
                $this->droppedTasks++;
                throw new RuntimeException('Task queue full, task dropped');
            }
        }

        // Apply backpressure
        if (
            $this->enableBackpressure &&
            $this->getQueueSize() >= $this->backpressureThreshold
        ) {
            usleep(1000); // 1ms delay
        }

        $task = CallableUtil::makeAllToCallable($task);
        $task = $this->taskPool->getTask($task, $context);
        $this->readyQueue->insert($task, $task->id);
        return $task->id;
    }

    public function select(callable|Generator ...$tasks): int
    {
        $chainId = self::$nextChainId++ >= PHP_INT_MAX ? 0 : self::$nextChainId;
        foreach ($tasks as $task) {
            // Check queue limit for each task
            if ($this->getQueueSize() >= $this->maxQueueSize) {
                break;
            }

            $task = CallableUtil::makeAllToCallable($task);
            $task = $this->taskPool->getTask($task);
            $task->chainId = $chainId;
            $this->readyQueue->insert($task, $task->id);
        }
        return $chainId;
    }

    public function run(): void
    {
        $this->startTime = time();
        $this->isRunning = true;

        while (
            $this->hasReadyTasks() ||
            $this->hasRunningTasks() ||
            !empty($this->chainedTasks)
        ) {
            if (!$this->isRunning) {
                break;
            }

            $this->resetCycleCounters();
            $this->processTasksWithLimits();
            $this->handleMemoryManagement();

            // Yield control briefly to prevent CPU hogging
            if ($this->shouldYieldControl()) {
                usleep(100); // 0.1ms
            }
        }

        $this->memoryManager?->collectGarbage();
    }

    private function resetCycleCounters(): void
    {
        $this->currentCycleTaskCount = 0;
        $this->cycleStartTime = microtime(true);
    }

    private function processTasksWithLimits(): void
    {
        // Process ready tasks with limits
        while ($this->hasReadyTasks() && $this->canProcessMoreTasks()) {
            $task = $this->readyQueue->extract();
            $this->executeTask($task);
            $this->currentCycleTaskCount++;
        }

        // Process running tasks with limits
        $processedCount = 0;
        foreach ($this->runningTasks as $task) {
            if (!$this->canProcessMoreTasks())
                break;

            $this->executeTask($task);
            $processedCount++;
            $this->currentCycleTaskCount++;
        }

        // Process chained tasks with limits
        foreach ($this->chainedTasks as $tasks) {
            foreach ($tasks as $task) {
                if (!$this->canProcessMoreTasks())
                    break 2;

                $this->executeTask($task);
                $this->currentCycleTaskCount++;
            }
        }
    }

    private function canProcessMoreTasks(): bool
    {
        if ($this->currentCycleTaskCount >= $this->maxTasksPerCycle) {
            return false;
        }

        $elapsed = microtime(true) - $this->cycleStartTime;
        if ($elapsed >= $this->maxExecutionTime) {
            return false;
        }

        return true;
    }

    private function shouldYieldControl(): bool
    {
        return $this->currentCycleTaskCount >= $this->maxTasksPerCycle ||
            (microtime(true) - $this->cycleStartTime) >= $this->maxExecutionTime;
    }

    private function handleMemoryManagement(): void
    {
        if ($this->memoryManager?->checkMemoryUsage()) {
            $this->memoryManager?->forceGarbageCollection();
        }
    }

    private function getQueueSize(): int
    {
        return $this->readyQueue->count();
    }

    public function close(): void
    {
        $this->isRunning = false;
    }

    public function setMaxTasksPerCycle(int $maxTasks): void
    {
        if ($maxTasks <= 0) {
            throw new InvalidArgumentException('Max tasks per cycle must be positive');
        }
        $this->maxTasksPerCycle = $maxTasks;
    }

    public function setMaxQueueSize(int $maxSize): void
    {
        if ($maxSize <= 0) {
            throw new InvalidArgumentException('Max queue size must be positive');
        }
        $this->maxQueueSize = $maxSize;
    }

    public function setMaxExecutionTime(float $maxTime): void
    {
        if ($maxTime <= 0) {
            throw new InvalidArgumentException('Max execution time must be positive');
        }
        $this->maxExecutionTime = $maxTime;
    }

    public function setBackpressureEnabled(bool $enabled): void
    {
        $this->enableBackpressure = $enabled;
    }

    public function setBackpressureThreshold(int $threshold): void
    {
        if ($threshold <= 0 || $threshold > $this->maxQueueSize) {
            throw new InvalidArgumentException('Invalid backpressure threshold');
        }
        $this->backpressureThreshold = $threshold;
    }

    // Stats methods
    public function getStats(): array
    {
        return [
            'queue_size' => $this->getQueueSize(),
            'running_tasks' => count($this->runningTasks),
            'chained_tasks' => count($this->chainedTasks),
            'deferred_tasks' => count($this->deferredTasks),
            'dropped_tasks' => $this->droppedTasks,
            'task_pool_stats' => $this->taskPool->getStats(),
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true)
        ];
    }

    private function hasReadyTasks(): bool
    {
        return !$this->readyQueue->isEmpty();
    }

    private function hasRunningTasks(): bool
    {
        return !empty($this->runningTasks);
    }

    private function executeTask(Task $task): void
    {
        $isDone = false;
        try {
            if ($task->state === TaskState::PENDING) {
                $task->state = TaskState::RUNNING;
                if ($task->chainId === null) {
                    $this->runningTasks[$task->id] = $task;
                } else {
                    $this->chainedTasks[$task->chainId][] = $task;
                }
                $task->callback = ($task->callback)($task->context, $this);
            }

            if ($task->state === TaskState::RUNNING) {
                if ($task->callback instanceof Generator) {
                    if (!$task->firstRun) {
                        $task->firstRun = true;
                    } else {
                        $task->callback->next();
                    }

                    $result = $task->callback->current();
                    if (!$task->callback->valid() || $result instanceof CancelFuture) {
                        $isDone = true;
                        $this->completeTask($task, $result);
                    }

                    if ($result instanceof Sleep || $result instanceof Interval) {
                        $task->sleep($result->seconds);
                    }

                    if ($result instanceof Defer) {
                        $this->deferredTasks[$task->id][] = $result;
                    }
                } else {
                    $isDone = true;
                    $this->completeTask($task, $task->callback);
                }
            } elseif ($task->state === TaskState::SLEEPING) {
                $task->tryWake();
            }
        } catch (Throwable $e) {
            $isDone = true;
            $this->failTask($task, $e);
        } finally {
            if (!$isDone) {
                $this->runningTasks[$task->id] = $task;
            }
        }
    }

    private function completeTask(Task $task, mixed $result = null): void
    {
        $task->state = TaskState::COMPLETED;
        $task->result = $result;
        if ($task->chainId !== null) {
            unset($this->chainedTasks[$task->chainId]);
        } else {
            unset($this->runningTasks[$task->id]);
        }

        // Return task to pool
        $this->taskPool->returnTask($task);

        if (!empty($this->deferredTasks[$task->id])) {
            foreach ($this->deferredTasks[$task->id] as $deferredTask) {
                $this->spawn($deferredTask->callback);
            }
            unset($this->deferredTasks[$task->id]);
        }
    }

    private function failTask(Task $task, Throwable $error): void
    {
        $task->state = TaskState::FAILED;
        $task->error = $error;
        if ($task->chainId !== null) {
            unset($this->chainedTasks[$task->chainId]);
        } else {
            unset($this->runningTasks[$task->id]);
        }

        // Return task to pool
        $this->taskPool->returnTask($task);

        if (!empty($this->deferredTasks[$task->id])) {
            unset($this->deferredTasks[$task->id]);
        }
    }
}