<?php

declare(strict_types=1);

namespace venndev\vosaka\task;

use venndev\vosaka\core\Result;

/**
 * Internal class to track individual tasks in a JoinSet
 */
final class JoinSetTask
{
    private bool $aborted = false;
    private bool $detached = false;
    private mixed $key = null;

    public function __construct(
        private int $id,
        private Result $result,
        private mixed $context = null
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getResult(): Result
    {
        return $this->result;
    }

    public function getContext(): mixed
    {
        return $this->context;
    }

    public function getKey(): mixed
    {
        return $this->key;
    }

    public function setKey(mixed $key): void
    {
        $this->key = $key;
    }

    public function abort(): void
    {
        $this->aborted = true;
    }

    public function detach(): void
    {
        $this->detached = true;
    }

    public function isAborted(): bool
    {
        return $this->aborted;
    }

    public function isDetached(): bool
    {
        return $this->detached;
    }
}
