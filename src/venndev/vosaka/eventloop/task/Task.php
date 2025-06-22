<?php

declare(strict_types=1);

namespace venndev\vosaka\eventloop\task;

use Closure;
use RuntimeException;
use Throwable;

final class Task
{
    public int $id;
    public ?int $chainId = null;
    public TaskState $state = TaskState::PENDING;
    public mixed $result = null;
    public ?Throwable $error = null;
    public float $wakeTime = 0.0;
    public mixed $callback;
    public mixed $context = null;
    public bool $firstRun = false;
    private static int $nextId = 0;

    public function __construct(callable $task, mixed $context = null)
    {
        $this->id = self::$nextId++ >= PHP_INT_MAX ? 0 : self::$nextId;
        $this->callback = Closure::fromCallable($task);
        $this->context = $context;
    }

    public function tryWake(): bool
    {
        if ($this->state === TaskState::SLEEPING && microtime(true) >= $this->wakeTime) {
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

    public function reset(): void
    {
        $this->state = TaskState::PENDING;
        $this->result = null;
        $this->error = null;
        $this->wakeTime = 0.0;
        $this->context = null;
        $this->callback = null;
    }
}