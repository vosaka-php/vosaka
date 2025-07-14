<?php

declare(strict_types=1);

namespace venndev\vosaka\eventloop\task;

use SplQueue;
use WeakMap;

final class TaskPool
{
    private SplQueue $availableTasks;
    private WeakMap $allTasks;
    private int $maxPoolSize;
    private int $created = 0;
    private int $reused = 0;
    private int $currentPoolSize = 0;

    public function __construct(int $maxPoolSize = 1000)
    {
        $this->availableTasks = new SplQueue();
        $this->allTasks = new WeakMap();
        $this->maxPoolSize = $maxPoolSize;
    }

    public function getTask(callable $callback, mixed $context = null): Task
    {
        if (!$this->availableTasks->isEmpty()) {
            $task = $this->availableTasks->dequeue();
            $task->callback = $callback;
            $task->context = $context;
            $task->state = TaskState::PENDING;
            $this->reused++;
            return $task;
        }

        if ($this->currentPoolSize < $this->maxPoolSize) {
            $task = new Task($callback, $context);
            $this->allTasks[$task] = true;
            $this->currentPoolSize++;
            $this->created++;
            return $task;
        }

        $this->created++;
        return new Task($callback, $context);
    }

    public function returnTask(Task $task): void
    {
        if (!isset($this->allTasks[$task])) {
            return;
        }

        if (!$task->reset()) {
            unset($this->allTasks[$task]);
            $this->currentPoolSize--;
            return;
        }

        $this->availableTasks->enqueue($task);
    }

    public function getStats(): array
    {
        return [
            "pool_size" => $this->currentPoolSize,
            "available_tasks" => $this->availableTasks->count(),
            "created" => $this->created,
            "reused" => $this->reused,
            "reuse_rate" => $this->created > 0
                ? round(($this->reused / $this->created) * 100, 2)
                : 0,
            "efficiency" => $this->created > 0
                ? round(($this->reused / ($this->created + $this->reused)) * 100, 2)
                : 0,
        ];
    }

    public function clear(): void
    {
        $this->availableTasks = new SplQueue();
        $this->allTasks = new WeakMap();
        $this->currentPoolSize = 0;
        $this->created = 0;
        $this->reused = 0;
    }

    /**
     * Warm up the task pool by creating a number of tasks.
     *
     * @param int|null $count The number of tasks to create. Defaults to 100 or max pool size.
     */
    public function warmUp(?int $count = null): void
    {
        $count = $count ?? min(100, $this->maxPoolSize);

        for ($i = 0; $i < $count; $i++) {
            if ($this->currentPoolSize >= $this->maxPoolSize) {
                break;
            }

            $task = new Task(fn() => null);
            $task->reset();
            $this->allTasks[$task] = true;
            $this->availableTasks->enqueue($task);
            $this->currentPoolSize++;
        }
    }
}
