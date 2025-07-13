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
 * This class focuses on task management and execution.
 */
final class TaskManager
{
    private TaskPool $taskPool;
    private SplQueue $runningTasks;
    private WeakMap $deferredTasks;

    public function __construct()
    {
        $this->taskPool = new TaskPool();
        $this->runningTasks = new SplQueue();
        $this->deferredTasks = new WeakMap();
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
     * Process running tasks
     */
    public function processRunningTasks(): void
    {
        $tasks = $this->runningTasks->count();
        while ($tasks--) {
            $task = $this->runningTasks->dequeue();

            try {
                $this->executeTask($task);
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
        $ioYield = $current instanceof Sleep ||
            $current instanceof Interval ||
            $current instanceof Defer ||
            $current instanceof CancelFuture;

        if ($current !== null && !$ioYield) {
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
     * Deferred task addition with pooling
     */
    private function addDeferredTask(Task $task, Defer $defer): void
    {
        if (!isset($this->deferredTasks[$task])) {
            $this->deferredTasks[$task] = [];
        }
        $this->deferredTasks[$task][] = $defer;
    }

    private function doDeferredTask(Task $task, mixed $result = null): void
    {
        if (!isset($this->deferredTasks[$task])) {
            return;
        }

        foreach ($this->deferredTasks[$task] as $deferredTask) {
            ($deferredTask->callback)($result);
        }

        unset($this->deferredTasks[$task]);
    }

    /**
     * Task completion with pooled arrays
     */
    private function completeTask(Task $task, mixed $result = null): void
    {
        $task->state = TaskState::COMPLETED;
        $this->taskPool->returnTask($task);
        $this->doDeferredTask($task, $result);
        JoinHandle::done($task->id, $result);
    }

    private function failTask(Task $task, Throwable $error): void
    {
        $task->state = TaskState::FAILED;
        $task->error = $error;
        $this->taskPool->returnTask($task);
        $this->doDeferredTask($task, $error);
        JoinHandle::done($task->id, $error);
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

    public function getTaskPoolStats(): array
    {
        return $this->taskPool->getStats();
    }

    public function reset(): void
    {
        $this->runningTasks = new SplQueue();
        $this->deferredTasks = new WeakMap();
    }
}
