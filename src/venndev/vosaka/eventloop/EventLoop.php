<?php

declare(strict_types=1);

namespace venndev\vosaka\eventloop;

use Generator;
use InvalidArgumentException;
use venndev\vosaka\cleanup\GracefulShutdown;
use venndev\vosaka\eventloop\task\TaskManager;

/**
 * This class focuses on the main event loop operations and coordination.
 */
final class EventLoop
{
    private TaskManager $taskManager;
    private StreamHandler $streamHandler;
    private ?GracefulShutdown $gracefulShutdown = null;
    private bool $isRunning = false;

    // Control the number of iterations
    private int $iterationLimit = 1;
    private int $currentIteration = 0;
    private bool $enableIterationLimit = false;

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
     * Spawn method - delegates to TaskManager
     */
    public function spawn(callable|Generator $task, mixed $context = null): int
    {
        return $this->taskManager->spawn($task, $context);
    }

    /**
     * Main run loop with stream support and batch processing
     */
    public function run(): void
    {
        $this->isRunning = true;

        while ($this->isRunning) {
            // Process tasks first - multiple rounds for high load
            $this->taskManager->processRunningTasks();

            // Check iteration limits
            if ($this->isLimitedToIterations()) {
                break;
            }

            // Handle streams - minimal blocking
            $timeout = $this->taskManager->hasRunningTasks() ? 0 : 1;
            $this->streamHandler->waitForStreamActivity($timeout);

            if ($this->shouldStop()) {
                break;
            }
        }
    }

    /**
     * Calculate timeout for stream_select
     */
    private function calculateSelectTimeout(): ?int
    {
        if ($this->taskManager->hasRunningTasks()) {
            return 0;
        }

        if (
            $this->streamHandler->hasStreams() ||
            $this->streamHandler->hasSignals()
        ) {
            return 1;
        }

        return 0;
    }

    /**
     * Check if event loop should stop
     */
    private function shouldStop(): bool
    {
        return !$this->taskManager->hasRunningTasks() &&
            !$this->streamHandler->hasStreams() &&
            !$this->streamHandler->hasSignals();
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
        return $this->enableIterationLimit &&
            $this->currentIteration++ >= $this->iterationLimit;
    }

    public function getStats(): array
    {
        return [
            "running_tasks" => $this->taskManager->getRunningTasksCount(),
            "deferred_tasks" => $this->taskManager->getDeferredTasksCount(),
            "stream_stats" => $this->streamHandler->getStats(),
            "task_pool_stats" => $this->taskManager->getTaskPoolStats(),
            "memory_usage" => memory_get_usage(true),
            "peak_memory" => memory_get_peak_usage(true),
        ];
    }
}
