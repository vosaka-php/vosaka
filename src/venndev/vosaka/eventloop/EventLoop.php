<?php

declare(strict_types=1);

namespace venndev\vosaka\eventloop;

use Generator;
use InvalidArgumentException;
use venndev\vosaka\cleanup\GracefulShutdown;
use venndev\vosaka\eventloop\task\TaskManager;

final class EventLoop
{
    private TaskManager $taskManager;
    private StreamHandler $streamHandler;
    private ?GracefulShutdown $gracefulShutdown = null;
    private bool $isRunning = false;

    // Performance optimization settings
    private int $maxTasksPerCycle = 100;
    private float $maxExecutionTime = 0.2;
    private int $currentCycleTaskCount = 0;
    private float $cycleStartTime = 0.0;

    // Control the number of iterations
    private int $iterationLimit = 1;
    private int $currentIteration = 0;
    private bool $enableIterationLimit = false;

    // Performance optimization caches
    private bool $hasTasksCache = false;
    private bool $hasStreamsCache = false;
    private int $cacheInvalidationCounter = 0;
    private int $streamCheckInterval = 10;
    private int $streamCheckCounter = 0;

    // Batch processing
    private int $batchSize = 50;
    private int $consecutiveEmptyCycles = 0;

    // Stats tracking
    private int $taskProcessedCount = 0;
    private float $startTime;

    public function __construct()
    {
        $this->taskManager = new TaskManager();
        $this->streamHandler = new StreamHandler();
    }

    public function getGracefulShutdown(): GracefulShutdown
    {
        return $this->gracefulShutdown ??= new GracefulShutdown();
    }

    public function getStreamHandler(): StreamHandler
    {
        return $this->streamHandler;
    }

    public function getTaskManager(): TaskManager
    {
        return $this->taskManager;
    }

    /**
     * Add a read stream to the event loop
     */
    public function addReadStream($stream, callable $listener): void
    {
        $this->streamHandler->addReadStream($stream, $listener);
        $this->invalidateStreamCache();
    }

    /**
     * Add a write stream to the event loop
     */
    public function addWriteStream($stream, callable $listener): void
    {
        $this->streamHandler->addWriteStream($stream, $listener);
        $this->invalidateStreamCache();
    }

    /**
     * Remove a read stream from the event loop
     */
    public function removeReadStream($stream): void
    {
        $this->streamHandler->removeReadStream($stream);
        $this->invalidateStreamCache();
    }

    /**
     * Remove a write stream from the event loop
     */
    public function removeWriteStream($stream): void
    {
        $this->streamHandler->removeWriteStream($stream);
        $this->invalidateStreamCache();
    }

    /**
     * Add signal handler
     */
    public function addSignal(int $signal, callable $listener): void
    {
        $this->streamHandler->addSignal($signal, $listener);
        $this->invalidateStreamCache();
    }

    /**
     * Remove signal handler
     */
    public function removeSignal(int $signal, callable $listener): void
    {
        $this->streamHandler->removeSignal($signal, $listener);
        $this->invalidateStreamCache();
    }

    /**
     * Spawn method - delegates to TaskManager
     */
    public function spawn(callable|Generator $task, mixed $context = null): int
    {
        $this->invalidateTaskCache();
        return $this->taskManager->spawn($task, $context);
    }

    /**
     * Optimized main run loop with batch processing
     */
    public function run(): void
    {
        $this->startTime = microtime(true);
        $this->isRunning = true;

        while ($this->isRunning && $this->hasWork()) {
            $this->resetCycleCounters();
            $this->processBatchTasks();

            if ($this->isLimitedToIterations()) {
                break;
            }

            $this->handleStreamActivity();
            $this->handleYielding();
        }
    }

    /**
     * Process tasks in batches for improved performance
     */
    private function processBatchTasks(): void
    {
        $tasksProcessed = 0;
        $batchLimit = min($this->batchSize, $this->maxTasksPerCycle);

        while (
            $tasksProcessed < $batchLimit &&
            $this->taskManager->hasRunningTasks() &&
            !$this->isTimeLimitExceeded()
        ) {
            $this->taskManager->processRunningTasks();

            $tasksProcessed += $this->taskManager->getLastProcessedCount();
            $this->currentCycleTaskCount = $tasksProcessed;
            $this->taskProcessedCount += $tasksProcessed;
        }

        if ($tasksProcessed === 0) {
            $this->consecutiveEmptyCycles++;
        } else {
            $this->consecutiveEmptyCycles = 0;
        }
    }

    /**
     * Smart stream handling with reduced overhead
     */
    private function handleStreamActivity(): void
    {
        if (!$this->hasStreams()) {
            return;
        }

        if ($this->hasTasksCached() && ++$this->streamCheckCounter % $this->streamCheckInterval !== 0) {
            return;
        }

        $timeout = $this->calculateStreamTimeout();
        $this->streamHandler->waitForStreamActivity($timeout);
        $this->streamCheckCounter = 0;
    }

    /**
     * Calculate optimal stream timeout based on current workload
     */
    private function calculateStreamTimeout(): int
    {
        if ($this->hasTasksCached()) {
            return 0;
        }

        if ($this->consecutiveEmptyCycles > 10) {
            return 10;
        } elseif ($this->consecutiveEmptyCycles > 5) {
            return 1;
        }

        return 0;
    }

    /**
     * Smart yielding with adaptive behavior
     */
    private function handleYielding(): void
    {
        if ($this->shouldYieldControl()) {
            if ($this->consecutiveEmptyCycles < 3) {
                return;
            }

            usleep($this->consecutiveEmptyCycles * 2);
        }
    }

    /**
     * Check if we should yield control
     */
    private function shouldYieldControl(): bool
    {
        return $this->currentCycleTaskCount >= $this->maxTasksPerCycle ||
            $this->isTimeLimitExceeded();
    }

    /**
     * Check if time limit exceeded
     */
    private function isTimeLimitExceeded(): bool
    {
        return microtime(true) - $this->cycleStartTime >= $this->maxExecutionTime;
    }

    /**
     * Reset cycle counters
     */
    private function resetCycleCounters(): void
    {
        $this->currentCycleTaskCount = 0;
        $this->cycleStartTime = microtime(true);
    }

    /**
     * Cached check for work
     */
    private function hasWork(): bool
    {
        return $this->hasTasksCached() ||
            $this->hasStreams() ||
            $this->streamHandler->hasSignals();
    }

    /**
     * Cached check for tasks
     */
    private function hasTasksCached(): bool
    {
        if ($this->cacheInvalidationCounter++ % 10 === 0) {
            $this->hasTasksCache = $this->taskManager->hasRunningTasks();
        }
        return $this->hasTasksCache;
    }

    /**
     * Cached check for streams
     */
    private function hasStreams(): bool
    {
        if ($this->cacheInvalidationCounter % 10 === 0) {
            $this->hasStreamsCache = $this->streamHandler->hasStreams();
        }
        return $this->hasStreamsCache;
    }

    /**
     * Invalidate task cache
     */
    private function invalidateTaskCache(): void
    {
        $this->hasTasksCache = true;
        $this->cacheInvalidationCounter = 0;
    }

    /**
     * Invalidate stream cache
     */
    private function invalidateStreamCache(): void
    {
        $this->hasStreamsCache = true;
        $this->cacheInvalidationCounter = 0;
    }

    public function stop(): void
    {
        $this->isRunning = false;
    }

    public function close(): void
    {
        $this->isRunning = false;
        $this->taskManager->reset();
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

    public function isLimitedToIterations(): bool
    {
        if ($this->enableIterationLimit) {
            if ($this->currentIteration >= $this->iterationLimit) {
                return true;
            }
            $this->currentIteration++;
        }
        return false;
    }

    // Performance tuning methods
    public function setMaxTasksPerCycle(int $maxTasks): void
    {
        if ($maxTasks <= 0) {
            throw new InvalidArgumentException(
                "Max tasks per cycle must be positive"
            );
        }
        $this->maxTasksPerCycle = $maxTasks;
        $this->batchSize = min($maxTasks, 100);
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

    public function setBatchSize(int $size): void
    {
        if ($size <= 0) {
            throw new InvalidArgumentException(
                "Batch size must be positive"
            );
        }
        $this->batchSize = $size;
    }

    public function setStreamCheckInterval(int $interval): void
    {
        if ($interval <= 0) {
            throw new InvalidArgumentException(
                "Stream check interval must be positive"
            );
        }
        $this->streamCheckInterval = $interval;
    }

    /**
     * Apply performance tuning for high-throughput scenarios
     */
    public function enableHighPerformanceMode(): void
    {
        $this->maxTasksPerCycle = 200;
        $this->maxExecutionTime = 0.5;
        $this->batchSize = 100;
        $this->streamCheckInterval = 50;
    }

    /**
     * Apply balanced tuning for mixed workloads
     */
    public function enableBalancedMode(): void
    {
        $this->maxTasksPerCycle = 100;
        $this->maxExecutionTime = 0.2;
        $this->batchSize = 50;
        $this->streamCheckInterval = 10;
    }

    /**
     * Apply stream-optimized tuning
     */
    public function enableStreamMode(): void
    {
        $this->maxTasksPerCycle = 50;
        $this->maxExecutionTime = 0.1;
        $this->batchSize = 25;
        $this->streamCheckInterval = 1;
    }

    public function getStats(): array
    {
        $runtime = microtime(true) - $this->startTime;

        return [
            "running_tasks" => $this->taskManager->getRunningTasksCount(),
            "deferred_tasks" => $this->taskManager->getDeferredTasksCount(),
            "stream_stats" => $this->streamHandler->getStats(),
            "task_pool_stats" => $this->taskManager->getTaskPoolStats(),
            "memory_usage" => memory_get_usage(true),
            "peak_memory" => memory_get_peak_usage(true),
            "total_tasks_processed" => $this->taskProcessedCount,
            "tasks_per_second" => $runtime > 0 ? $this->taskProcessedCount / $runtime : 0,
            "consecutive_empty_cycles" => $this->consecutiveEmptyCycles,
            "batch_size" => $this->batchSize,
            "stream_check_interval" => $this->streamCheckInterval,
        ];
    }
}
