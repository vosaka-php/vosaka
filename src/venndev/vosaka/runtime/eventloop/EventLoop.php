<?php

declare(strict_types=1);

namespace venndev\vosaka\runtime\eventloop;

use Generator;
use InvalidArgumentException;
use RuntimeException;
use SplPriorityQueue;
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

final class EventLoop
{
    private SplPriorityQueue $readyQueue;
    private TaskPool $taskPool;
    private ?MemoryManager $memoryManager = null;
    private ?GracefulShutdown $gracefulShutdown = null;
    private array $runningTasks = [];
    private array $deferredTasks = [];
    private bool $isRunning = false;

    // Memory monitoring
    private int $maxMemoryUsage;
    private int $taskProcessedCount = 0;
    private float $startTime;

    // Optimized limits
    private int $maxTasksPerCycle = 15;
    private int $maxQueueSize = 4000;
    private float $maxExecutionTime = 0.08;
    private int $currentCycleTaskCount = 0;
    private float $cycleStartTime = 0.0;

    // Backpressure handling
    private bool $enableBackpressure = true;
    private int $backpressureThreshold = 3200; // 80% of max queue
    private int $droppedTasks = 0;

    // Control the number of iterations
    private int $iterationLimit = 1;
    private bool $enableIterationLimit = false;

    // Cache queue size to avoid repeated calls
    private int $queueSize = 0;

    public function __construct(int $maxMemoryMB = 128) // Reduced default memory limit
    {
        $this->readyQueue = new SplPriorityQueue();
        $this->taskPool = new TaskPool();
        $this->maxMemoryUsage = MemUtil::toKB($maxMemoryMB);
    }

    public function getMemoryManager(): MemoryManager
    {
        return $this->memoryManager ??= new MemoryManager($this->maxMemoryUsage);
    }

    public function getGracefulShutdown(): GracefulShutdown
    {
        return $this->gracefulShutdown ??= new GracefulShutdown();
    }

    public function spawn(callable|Generator $task, mixed $context = null): int
    {
        // Check queue size limit
        if ($this->queueSize >= $this->maxQueueSize) {
            if ($this->enableBackpressure) {
                $this->droppedTasks++;
                throw new RuntimeException('Task queue full, task dropped');
            }
        }

        // Apply backpressure
        if ($this->enableBackpressure && $this->queueSize >= $this->backpressureThreshold) {
            usleep(500); // Reduced delay to 0.5ms
        }

        $task = CallableUtil::makeAllToCallable($task);
        $task = $this->taskPool->getTask($task, $context);
        $this->readyQueue->insert($task, -$task->id);
        $this->queueSize++;

        return $task->id;
    }

    public function run(): void
    {
        $this->startTime = microtime(true);
        $this->isRunning = true;

        while ($this->queueSize > 0 || !empty($this->runningTasks) || !empty($this->deferredTasks)) {
            if (!$this->isRunning) {
                break;
            }

            $this->resetCycleCounters();
            $this->processTasksWithLimits();
            $this->handleMemoryManagement();

            // Yield control only when necessary
            if ($this->shouldYieldControl()) {
                usleep(50); // Reduced to 0.05ms for less overhead
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
        // Process ready tasks
        while ($this->queueSize > 0 && $this->canProcessMoreTasks()) {
            $task = $this->readyQueue->extract();
            $this->queueSize--;
            $this->executeTask($task);
            $this->currentCycleTaskCount++;
        }

        // Process running tasks
        $runningTasks = $this->runningTasks; // Cache to avoid modifying array during iteration
        foreach ($runningTasks as $taskId => $task) {
            if (!$this->canProcessMoreTasks()) {
                break;
            }
            $this->executeTask($task);
            $this->currentCycleTaskCount++;
        }
    }

    private function canProcessMoreTasks(): bool
    {
        return $this->currentCycleTaskCount < $this->maxTasksPerCycle &&
            (microtime(true) - $this->cycleStartTime) < $this->maxExecutionTime;
    }

    private function shouldYieldControl(): bool
    {
        return $this->currentCycleTaskCount >= $this->maxTasksPerCycle ||
            (microtime(true) - $this->cycleStartTime) >= $this->maxExecutionTime;
    }

    private function handleMemoryManagement(): void
    {
        if ($this->memoryManager?->checkMemoryUsage()) {
            $this->memoryManager->forceGarbageCollection();
            // Clear unused deferred tasks
            $this->deferredTasks = array_filter($this->deferredTasks, fn($tasks) => !empty($tasks));
        }
    }

    private function getQueueSize(): int
    {
        return $this->queueSize;
    }

    public function close(): void
    {
        $this->isRunning = false;
        $this->queueSize = 0; // Reset cached size
        $this->readyQueue = new SplPriorityQueue(); // Clear queue
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
        $this->backpressureThreshold = (int) ($maxSize * 0.8); // Update threshold
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

    public function getStats(): array
    {
        return [
            'queue_size' => $this->queueSize,
            'running_tasks' => count($this->runningTasks),
            'deferred_tasks' => count($this->deferredTasks),
            'dropped_tasks' => $this->droppedTasks,
            'task_pool_stats' => $this->taskPool->getStats(),
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true)
        ];
    }

    private function hasReadyTasks(): bool
    {
        return $this->queueSize > 0;
    }

    private function hasRunningTasks(): bool
    {
        return !empty($this->runningTasks);
    }

    private function executeTask(Task $task): void
    {
        $result = null;
        $isDone = false;

        try {
            if ($task->state === TaskState::PENDING) {
                $task->state = TaskState::RUNNING;
                $this->runningTasks[$task->id] = $task;
                $task->callback = ($task->callback)($task->context, $this);
            }

            if ($task->state === TaskState::RUNNING) {
                if ($task->callback instanceof Generator) {
                    if (!$task->firstRun) {
                        $task->firstRun = true;
                    } else {
                        if ($task->callback->valid()) {
                            $task->callback->next();
                        }
                    }

                    if (!$task->callback->valid()) {
                        $isDone = true;
                        $result = GeneratorUtil::getReturnSafe($task->callback);
                        $this->completeTask($task, $result);
                    } else {
                        $current = $task->callback->current();
                        if ($current instanceof CancelFuture) {
                            $isDone = true;
                            $result = GeneratorUtil::getReturnSafe($task->callback);
                            $this->completeTask($task, $result);
                        } elseif ($current instanceof Sleep || $current instanceof Interval) {
                            $task->sleep($current->seconds);
                        } elseif ($current instanceof Defer) {
                            $this->deferredTasks[$task->id][] = $current;
                        }
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
            $result = $e;
            $this->failTask($task, $e);
        } finally {
            if ($isDone) {
                unset($this->runningTasks[$task->id]);
                JoinHandle::done($task->id, $result);
            }
        }
    }

    private function completeTask(Task $task, mixed $result = null): void
    {
        $task->state = TaskState::COMPLETED;
        $task->result = $result;
        $this->taskPool->returnTask($task);

        if (!empty($this->deferredTasks[$task->id])) {
            foreach ($this->deferredTasks[$task->id] as $deferredTask) {
                ($deferredTask->callback)($result);
            }
            unset($this->deferredTasks[$task->id]);
        }
    }

    private function failTask(Task $task, Throwable $error): void
    {
        $task->state = TaskState::FAILED;
        $task->error = $error;
        $this->taskPool->returnTask($task);
        unset($this->deferredTasks[$task->id]);
    }
}
