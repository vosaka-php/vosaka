<?php

declare(strict_types=1);

namespace venndev\vosaka\eventloop\task;

use Generator;
use WeakMap;
use SplQueue;
use Throwable;
use venndev\vosaka\io\JoinHandle;
use venndev\vosaka\eventloop\task\TaskPool;
use venndev\vosaka\eventloop\task\TaskState;
use venndev\vosaka\eventloop\task\Task;
use venndev\vosaka\time\Interval;
use venndev\vosaka\time\Sleep;
use venndev\vosaka\core\Defer;
use venndev\vosaka\utils\CallableUtil;
use venndev\vosaka\utils\GeneratorUtil;
use venndev\vosaka\utils\sync\CancelFuture;

/**
 * Optimized TaskManager with batch processing and performance improvements
 */
final class TaskManager
{
    private TaskPool $taskPool;
    private SplQueue $runningTasks;
    private WeakMap $deferredTasks;

    // Performance tracking
    private int $lastProcessedCount = 0;

    // Object pools for memory optimization
    private array $deferredArrayPool = [];
    private array $taskBatchPool = [];

    // Batch processing
    private int $maxBatchSize = 50;

    public function __construct()
    {
        $this->taskPool = new TaskPool();
        $this->runningTasks = new SplQueue();
        $this->deferredTasks = new WeakMap();
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
            $this->taskBatchPool[] = [];
        }
    }

    /**
     * Get a pooled array for deferred tasks
     */
    private function getPooledDeferredArray(): array
    {
        return array_pop($this->deferredArrayPool) ?? [];
    }

    /**
     * Return an array to the pool
     */
    private function returnPooledDeferredArray(array $arr): void
    {
        if (count($this->deferredArrayPool) < 50) {
            array_splice($arr, 0); // Clear array
            $this->deferredArrayPool[] = $arr;
        }
    }

    /**
     * Get a pooled batch array
     */
    private function getPooledBatchArray(): array
    {
        return array_pop($this->taskBatchPool) ?? [];
    }

    /**
     * Return batch array to pool
     */
    private function returnPooledBatchArray(array $arr): void
    {
        if (count($this->taskBatchPool) < 20) {
            array_splice($arr, 0); // Clear array
            $this->taskBatchPool[] = $arr;
        }
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
     * Process running tasks with batch optimization
     */
    public function processRunningTasks(): void
    {
        $this->lastProcessedCount = 0;
        $count = $this->runningTasks->count();

        if ($count === 0) {
            return;
        }

        $batchSize = min($count, $this->maxBatchSize);
        $batch = $this->getPooledBatchArray();
        for ($i = 0; $i < $batchSize; $i++) {
            if ($this->runningTasks->isEmpty()) {
                break;
            }
            $batch[] = $this->runningTasks->dequeue();
        }

        foreach ($batch as $task) {
            try {
                $this->executeTask($task);
                $this->lastProcessedCount++;
            } catch (Throwable $e) {
                $this->failTask($task, $e);
            }
        }

        $this->returnPooledBatchArray($batch);
    }

    /**
     * Task execution with reduced overhead
     */
    private function executeTask(Task $task): void
    {
        if ($task->callback === null) {
            $this->completeTask($task);
            return;
        }

        switch ($task->state) {
            case TaskState::PENDING:
                $task->state = TaskState::RUNNING;
                $task->callback = ($task->callback)($task->context, $this);
                break;

            case TaskState::RUNNING:
                if ($task->callback instanceof Generator) {
                    $this->handleGenerator($task);
                } else {
                    $this->completeTask($task, $task->callback);
                    return;
                }
                break;

            case TaskState::SLEEPING:
                $task->tryWake();
                break;
        }

        if (
            $task->state !== TaskState::COMPLETED &&
            $task->state !== TaskState::FAILED
        ) {
            $this->runningTasks->enqueue($task);
        }
    }

    /**
     * Optimized generator handling
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
        $isIoYield = $current instanceof Sleep ||
            $current instanceof Interval ||
            $current instanceof Defer ||
            $current instanceof CancelFuture;

        if ($current !== null && !$isIoYield) {
            JoinHandle::tryYield($task->id, $current);
        }

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
     * Optimized deferred task addition with pooling
     */
    private function addDeferredTask(Task $task, Defer $defer): void
    {
        if (!isset($this->deferredTasks[$task])) {
            $this->deferredTasks[$task] = $this->getPooledDeferredArray();
        }
        $this->deferredTasks[$task][] = $defer;
    }

    /**
     * Process deferred tasks efficiently
     */
    private function processDeferredTasks(Task $task, mixed $result = null): void
    {
        if (!isset($this->deferredTasks[$task])) {
            return;
        }

        $deferredArray = $this->deferredTasks[$task];

        foreach ($deferredArray as $deferredTask) {
            ($deferredTask->callback)($result);
        }

        unset($this->deferredTasks[$task]);
        $this->returnPooledDeferredArray($deferredArray);
    }

    /**
     * Task completion with pooled arrays
     */
    private function completeTask(Task $task, mixed $result = null): void
    {
        $task->state = TaskState::COMPLETED;
        $this->processDeferredTasks($task, $result);
        JoinHandle::done($task->id, $result);
        $this->taskPool->returnTask($task);
    }

    /**
     * Task failure handling
     */
    private function failTask(Task $task, Throwable $error): void
    {
        $task->state = TaskState::FAILED;
        $task->error = $error;
        $this->processDeferredTasks($task, $error);
        JoinHandle::done($task->id, $error);
        $this->taskPool->returnTask($task);
    }

    public function hasRunningTasks(): bool
    {
        return !$this->runningTasks->isEmpty();
    }

    public function getRunningTasksCount(): int
    {
        return $this->runningTasks->count();
    }

    public function getDeferredTasksCount(): int
    {
        return $this->deferredTasks->count();
    }

    public function getLastProcessedCount(): int
    {
        return $this->lastProcessedCount;
    }

    public function getTaskPoolStats(): array
    {
        $stats = $this->taskPool->getStats();
        $stats['deferred_pool_size'] = count($this->deferredArrayPool);
        $stats['batch_pool_size'] = count($this->taskBatchPool);
        return $stats;
    }

    public function setMaxBatchSize(int $size): void
    {
        $this->maxBatchSize = max(1, $size);
    }

    public function reset(): void
    {
        $this->runningTasks = new SplQueue();
        $this->deferredTasks = new WeakMap();
        $this->lastProcessedCount = 0;

        // Clear pools
        $this->deferredArrayPool = [];
        $this->taskBatchPool = [];
        $this->initializePools();
    }
}
