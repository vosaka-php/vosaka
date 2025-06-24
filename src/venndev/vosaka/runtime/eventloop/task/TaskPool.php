<?php

declare(strict_types=1);

namespace venndev\vosaka\runtime\eventloop\task;

use SplQueue;

final class TaskPool
{
    private SplQueue $pool;
    private int $maxPoolSize;
    private int $created = 0;
    private int $reused = 0;

    public function __construct(int $maxPoolSize = 1000)
    {
        $this->pool = new SplQueue();
        $this->maxPoolSize = $maxPoolSize;
    }

    public function getTask(callable $callback, mixed $context = null): Task
    {
        if (!$this->pool->isEmpty()) {
            /**
             * @var Task $task
             */
            $task = $this->pool->dequeue();
            $task->callback = $callback;
            $task->context = $context;
            $this->reused++;
            return $task;
        }

        $this->created++;
        return new Task($callback, $context);
    }

    public function returnTask(Task $task): void
    {
        if ($this->pool->count() < $this->maxPoolSize) {
            $task->reset();
            $this->pool->enqueue($task);
        }
    }

    public function getStats(): array
    {
        return [
            'pool_size' => $this->pool->count(),
            'created' => $this->created,
            'reused' => $this->reused,
            'reuse_rate' => $this->created > 0 ?
                round($this->reused / $this->created * 100, 2) : 0
        ];
    }
}