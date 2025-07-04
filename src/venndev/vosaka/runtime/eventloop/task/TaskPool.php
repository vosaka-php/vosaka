<?php

declare(strict_types=1);

namespace venndev\vosaka\runtime\eventloop\task;

use WeakMap;

final class TaskPool
{
    private WeakMap $pool;
    private array $freeList = [];
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
        if (!empty($this->freeList)) {
            /** @var Task $task */
            $task = array_pop($this->freeList);
            $task->callback = $callback;
            $task->context = $context;
            $this->reused++;
            return $task;
        }

        $task = new Task($callback, $context);
        $this->pool[$task] = $task;
        $this->created++;
        return $task;
    }

    public function returnTask(Task $task): void
    {
        if (count($this->freeList) < $this->maxPoolSize) {
            $task->reset();
            $this->freeList[] = $task;
        }
    }

    public function getStats(): array
    {
        return [
            "pool_size" => count($this->freeList),
            "created" => $this->created,
            "reused" => $this->reused,
            "reuse_rate" =>
                $this->created > 0
                    ? round(($this->reused / $this->created) * 100, 2)
                    : 0,
        ];
    }
}
