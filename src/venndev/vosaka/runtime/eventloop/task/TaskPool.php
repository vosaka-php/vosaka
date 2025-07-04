<?php

declare(strict_types=1);

namespace venndev\vosaka\runtime\eventloop\task;

use WeakMap;

final class TaskPool
{
    private WeakMap $pool;
    private int $maxPoolSize;
    private int $created = 0;
    private int $reused = 0;

    public function __construct(int $maxPoolSize = 1000)
    {
        $this->pool = new WeakMap();
        $this->maxPoolSize = $maxPoolSize;
    }

    public function getTask(callable $callback, mixed $context = null): Task
    {
        foreach ($this->pool as $task) {
            if ($task->state === TaskState::PENDING) {
                $task->callback = $callback;
                $task->context = $context;
                $this->reused++;
                unset($this->pool[$task]);
                return $task;
            }
        }

        $this->created++;
        return new Task($callback, $context);
    }

    public function returnTask(Task $task): void
    {
        if (count($this->pool) < $this->maxPoolSize) {
            $task->reset();
            $this->pool[$task] = $task;
        }
    }

    public function getStats(): array
    {
        return [
            "pool_size" => count($this->pool),
            "created" => $this->created,
            "reused" => $this->reused,
            "reuse_rate" =>
                $this->created > 0
                    ? round(($this->reused / $this->created) * 100, 2)
                    : 0,
        ];
    }
}
