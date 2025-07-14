<?php

declare(strict_types=1);

namespace venndev\vosaka\eventloop\task;

use Closure;
use Throwable;

final class Task
{
    public int $id;
    public TaskState $state = TaskState::PENDING;
    public ?Throwable $error = null;
    public float $wakeTime = 0.0;
    public mixed $callback = null;
    public mixed $context = null;
    public bool $firstRun = false;
    private static int $nextId = 0;

    public function __construct(callable $task, mixed $context = null)
    {
        $this->id = self::$nextId++;
        $this->callback = Closure::fromCallable($task);
        $this->context = $context;
    }

    public function tryWake(): bool
    {
        if (
            $this->state === TaskState::SLEEPING &&
            microtime(true) >= $this->wakeTime
        ) {
            $this->state = TaskState::RUNNING;
            return true;
        }
        return false;
    }

    public function sleep(float $seconds): void
    {
        $this->state = TaskState::SLEEPING;
        $this->wakeTime = microtime(true) + $seconds;
    }

    public function reset(): bool
    {
        try {
            $this->state = TaskState::PENDING;
            $this->error = null;
            $this->wakeTime = 0.0;
            $this->context = null;
            $this->callback = null;
            return true;
        } catch (Throwable) {
            return false;
        }
    }
}
