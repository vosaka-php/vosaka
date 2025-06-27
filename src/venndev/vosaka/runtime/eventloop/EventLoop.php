<?php

declare(strict_types=1);

namespace venndev\vosaka\runtime\eventloop;

use Generator;
use SplPriorityQueue;
use WeakMap;
use InvalidArgumentException;
use RuntimeException;
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
 *  EventLoop class for high-performance asynchronous task execution.
 *
 * This enhanced version includes multiple performance optimizations:
 * - Batch processing for reduced overhead
 * - Memory pooling for object reuse
 * - Adaptive algorithms for smart resource management
 * - Micro-optimizations for hot paths
 * - Reduced method calls and improved caching
 */
final class EventLoop
{
    private SplPriorityQueue $readyQueue;
    private TaskPool $taskPool;
    private ?MemoryManager $memoryManager = null;
    private ?GracefulShutdown $gracefulShutdown = null;
    private WeakMap $runningTasks;
    private WeakMap $deferredTasks;
    private bool $isRunning = false;

    // Memory monitoring
    private int $maxMemoryUsage;
    private int $taskProcessedCount = 0;
    private float $startTime;

    //  limits - Increased for better performance
    private int $maxTasksPerCycle = 100;
    private int $maxQueueSize = 10000;
    private float $maxExecutionTime = 0.2;
    private int $currentCycleTaskCount = 0;
    private float $cycleStartTime = 0.0;

    // Backpressure handling
    private bool $enableBackpressure = true;
    private int $backpressureThreshold = 8000;
    private int $droppedTasks = 0;

    // Control the number of iterations
    private int $iterationLimit = 1;
    private int $currentIteration = 0;
    private bool $enableIterationLimit = false;

    // Cache queue size to avoid repeated calls
    private int $queueSize = 0;

    // Performance optimization caches
    private bool $hasRunningTasksCache = false;
    private bool $hasDeferredTasksCache = false;
    private int $cacheInvalidationCounter = 0;
    private int $memoryCheckCounter = 0;
    private int $memoryCheckInterval = 50;

    // Object pools for memory optimization
    private array $deferredArrayPool = [];
    private array $batchTasksPool = [];

    // Batch processing
    private int $batchSize = 50;
    private int $yieldCounter = 0;

    public function __construct(int $maxMemoryMB = 128)
    {
        $this->readyQueue = new SplPriorityQueue();
        $this->taskPool = new TaskPool();
        $this->maxMemoryUsage = MemUtil::toKB($maxMemoryMB);
        $this->runningTasks = new WeakMap();
        $this->deferredTasks = new WeakMap();

        // Pre-allocate pools
        $this->initializePools();
    }

    /**
     * Initialize object pools for memory optimization
     */
    private function initializePools(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $this->deferredArrayPool[] = [];
        }
        for ($i = 0; $i < 10; $i++) {
            $this->batchTasksPool[] = [];
        }
    }

    /**
     * Get a pooled array for deferred tasks
     */
    private function getPooledArray(): array
    {
        return array_pop($this->deferredArrayPool) ?? [];
    }

    /**
     * Return an array to the pool
     */
    private function returnPooledArray(array $arr): void
    {
        if (count($this->deferredArrayPool) < 50) {
            $this->deferredArrayPool[] = [];
        }
    }

    /**
     * Get a pooled batch array
     */
    private function getPooledBatchArray(): array
    {
        return array_pop($this->batchTasksPool) ?? [];
    }

    /**
     * Return batch array to pool
     */
    private function returnPooledBatchArray(array $arr): void
    {
        if (count($this->batchTasksPool) < 20) {
            $this->batchTasksPool[] = [];
        }
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

    /**
     *  spawn method with fast path for common cases
     */
    public function spawn(callable|Generator $task, mixed $context = null): int
    {
        // Fast path for common case (no backpressure)
        if ($this->queueSize < $this->backpressureThreshold) {
            $taskObj = $this->taskPool->getTask(
                CallableUtil::makeAllToCallable($task),
                $context
            );
            $this->readyQueue->insert($taskObj, -$taskObj->id);
            $this->queueSize++;
            return $taskObj->id;
        }

        // Slow path with backpressure handling
        if ($this->queueSize >= $this->maxQueueSize) {
            if ($this->enableBackpressure) {
                $this->droppedTasks++;
                throw new RuntimeException("Task queue full, task dropped");
            }
        }

        // Adaptive backpressure delay
        if ($this->enableBackpressure) {
            $delay = min(
                1000,
                ($this->queueSize - $this->backpressureThreshold) * 5
            );
            usleep($delay);
        }

        $taskObj = $this->taskPool->getTask(
            CallableUtil::makeAllToCallable($task),
            $context
        );
        $this->readyQueue->insert($taskObj, -$taskObj->id);
        $this->queueSize++;
        return $taskObj->id;
    }

    /**
     *  main run loop with batch processing and reduced overhead
     */
    public function run(): void
    {
        $this->startTime = microtime(true);
        $this->isRunning = true;

        while ($this->isRunning && $this->hasWork()) {
            $this->resetCycleCounters();
            $this->processBatchTasks();
            $this->processRunningTasks();
            $this->handleMemoryManagement();
            $this->handleYielding();

            if ($this->isLimitedToIterations()) {
                break;
            }
        }

        $this->memoryManager?->collectGarbage();
    }

    /**
     *  check for remaining work
     */
    private function hasWork(): bool
    {
        return $this->queueSize > 0 ||
            $this->hasRunningTasks() ||
            $this->hasDeferredTasks();
    }

    /**
     * Cached check for running tasks
     */
    private function hasRunningTasks(): bool
    {
        if ($this->cacheInvalidationCounter++ % 10 === 0) {
            $this->hasRunningTasksCache =
                $this->fastWeakMapCount($this->runningTasks) > 0;
        }
        return $this->hasRunningTasksCache;
    }

    /**
     * Cached check for deferred tasks
     */
    private function hasDeferredTasks(): bool
    {
        if ($this->cacheInvalidationCounter % 10 === 0) {
            $this->hasDeferredTasksCache =
                $this->fastWeakMapCount($this->deferredTasks) > 0;
        }
        return $this->hasDeferredTasksCache;
    }

    /**
     * Fast count for WeakMap with early exit
     */
    private function fastWeakMapCount(WeakMap $map): int
    {
        $count = 0;
        foreach ($map as $item) {
            $count++;
            if ($count > 5) {
                return $count;
            }
        }
        return $count;
    }

    /**
     * Process tasks in batches for improved performance
     */
    private function processBatchTasks(): void
    {
        if ($this->queueSize === 0) {
            return;
        }

        $batchSize = min(
            $this->batchSize,
            $this->queueSize,
            $this->maxTasksPerCycle
        );
        $tasks = $this->getPooledBatchArray();

        // Extract batch of tasks
        for ($i = 0; $i < $batchSize; $i++) {
            if ($this->queueSize <= 0) {
                break;
            }

            $tasks[] = $this->readyQueue->extract();
            $this->queueSize--;
        }

        // Process batch with error handling
        foreach ($tasks as $task) {
            try {
                $this->executeTask($task);
                $this->currentCycleTaskCount++;

                // Check time limit every 20 tasks
                if (
                    $this->currentCycleTaskCount % 20 === 0 &&
                    microtime(true) - $this->cycleStartTime >=
                        $this->maxExecutionTime
                ) {
                    break;
                }
            } catch (Throwable $e) {
                $this->failTask($task, $e);
            }
        }

        // Clear and return batch array to pool
        array_splice($tasks, 0);
        $this->returnPooledBatchArray($tasks);
    }

    /**
     * Process running tasks
     */
    private function processRunningTasks(): void
    {
        if (!$this->hasRunningTasksCache) {
            return;
        }

        $processed = 0;
        $maxRunningTasks = min(
            20,
            $this->maxTasksPerCycle - $this->currentCycleTaskCount
        );

        foreach ($this->runningTasks as $task) {
            if ($processed >= $maxRunningTasks) {
                break;
            }

            try {
                $this->executeTask($task);
                $processed++;
                $this->currentCycleTaskCount++;

                if (
                    microtime(true) - $this->cycleStartTime >=
                    $this->maxExecutionTime
                ) {
                    break;
                }
            } catch (Throwable $e) {
                $this->failTask($task, $e);
            }
        }
    }

    /**
     * Task execution with reduced overhead
     */
    private function executeTask(Task $task): void
    {
        if ($task->state === TaskState::PENDING) {
            $task->state = TaskState::RUNNING;
            $this->runningTasks[$task] = $task;
            $task->callback = ($task->callback)($task->context, $this);
            return;
        }

        if ($task->state === TaskState::RUNNING) {
            if ($task->callback instanceof Generator) {
                $this->handleGenerator($task);
            } else {
                $this->completeTask($task, $task->callback);
            }
        } elseif ($task->state === TaskState::SLEEPING) {
            $task->tryWake();
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

        match (true) {
            $current instanceof CancelFuture => $this->completeTask(
                $task,
                GeneratorUtil::getReturnSafe($generator)
            ),
            $current instanceof Sleep,
            $current instanceof Interval
                => $task->sleep($current->seconds),
            $current instanceof Defer => $this->addDeferredTask(
                $task,
                $current
            ),
            default => null,
        };
    }

    /**
     * Deferred task addition with pooling
     */
    private function addDeferredTask(Task $task, Defer $defer): void
    {
        if (!isset($this->deferredTasks[$task])) {
            $this->deferredTasks[$task] = $this->getPooledArray();
        }
        $this->deferredTasks[$task][] = $defer;
    }

    /**
     * Memory management with reduced frequency
     */
    private function handleMemoryManagement(): void
    {
        if (++$this->memoryCheckCounter % $this->memoryCheckInterval === 0) {
            if ($this->memoryManager?->checkMemoryUsage()) {
                $this->memoryManager->forceGarbageCollection();
            }
        }
    }

    /**
     * Smart yielding with adaptive behavior
     */
    private function handleYielding(): void
    {
        if ($this->shouldYieldControl()) {
            if (++$this->yieldCounter % 200 === 0) {
                usleep(1); // Minimal yield time
            }
        }
    }

    private function resetCycleCounters(): void
    {
        $this->currentCycleTaskCount = 0;
        $this->cycleStartTime = microtime(true);
    }

    private function shouldYieldControl(): bool
    {
        return $this->currentCycleTaskCount >= $this->maxTasksPerCycle ||
            microtime(true) - $this->cycleStartTime >= $this->maxExecutionTime;
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
            $this->returnPooledArray($deferredArray);
        }

        JoinHandle::done($task->id, $result);
        unset($this->runningTasks[$task]);
    }

    private function failTask(Task $task, Throwable $error): void
    {
        $task->state = TaskState::FAILED;
        $task->error = $error;
        $this->taskPool->returnTask($task);

        if (isset($this->deferredTasks[$task])) {
            $this->returnPooledArray($this->deferredTasks[$task]);
            unset($this->deferredTasks[$task]);
        }

        JoinHandle::done($task->id, $error);
        unset($this->runningTasks[$task]);
    }

    public function close(): void
    {
        $this->isRunning = false;
        $this->queueSize = 0;
        $this->readyQueue = new SplPriorityQueue();
    }

    // Configuration methods with optimized defaults
    public function setMaxTasksPerCycle(int $maxTasks): void
    {
        if ($maxTasks <= 0) {
            throw new InvalidArgumentException(
                "Max tasks per cycle must be positive"
            );
        }
        $this->maxTasksPerCycle = $maxTasks;
        $this->batchSize = min($maxTasks, 100); // Adaptive batch size
    }

    public function setMaxQueueSize(int $maxSize): void
    {
        if ($maxSize <= 0) {
            throw new InvalidArgumentException(
                "Max queue size must be positive"
            );
        }
        $this->maxQueueSize = $maxSize;
        $this->backpressureThreshold = (int) ($maxSize * 0.8);
    }

    public function setMaxExecutionTime(float $maxTime): void
    {
        if ($maxTime <= 0) {
            throw new InvalidArgumentException(
                "Max execution time must be positive"
            );
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
            throw new InvalidArgumentException(
                "Invalid backpressure threshold"
            );
        }
        $this->backpressureThreshold = $threshold;
    }

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

    /**
     * Enhanced statistics with performance metrics
     */
    public function getStats(): array
    {
        return [
            "queue_size" => $this->queueSize,
            "running_tasks" => $this->fastWeakMapCount($this->runningTasks),
            "deferred_tasks" => $this->fastWeakMapCount($this->deferredTasks),
            "dropped_tasks" => $this->droppedTasks,
            "task_pool_stats" => $this->taskPool->getStats(),
            "memory_usage" => memory_get_usage(true),
            "peak_memory" => memory_get_peak_usage(true),
            "batch_size" => $this->batchSize,
            "cycle_task_count" => $this->currentCycleTaskCount,
            "memory_check_interval" => $this->memoryCheckInterval,
            "pool_sizes" => [
                "deferred_arrays" => count($this->deferredArrayPool),
                "batch_arrays" => count($this->batchTasksPool),
            ],
        ];
    }

    /**
     * Apply performance tuning for high-throughput scenarios
     */
    public function enableHighPerformanceMode(): void
    {
        $this->maxTasksPerCycle = 200;
        $this->maxExecutionTime = 0.5;
        $this->maxQueueSize = 20000;
        $this->backpressureThreshold = 16000;
        $this->batchSize = 100;
        $this->memoryCheckInterval = 100;
    }

    /**
     * Apply conservative tuning for memory-constrained environments
     */
    public function enableMemoryConservativeMode(): void
    {
        $this->maxTasksPerCycle = 50;
        $this->maxExecutionTime = 0.1;
        $this->maxQueueSize = 2000;
        $this->backpressureThreshold = 1600;
        $this->batchSize = 25;
        $this->memoryCheckInterval = 10;
    }

    private function hasReadyTasks(): bool
    {
        return $this->queueSize > 0;
    }

    private function getQueueSize(): int
    {
        return $this->queueSize;
    }
}
