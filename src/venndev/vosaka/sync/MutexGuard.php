<?php

declare(strict_types=1);

namespace venndev\vosaka\sync;

/**
 * MutexGuard - RAII-style lock guard
 */
class MutexGuard
{
    private Mutex $mutex;
    private string $taskId;
    private bool $released = false;

    public function __construct(Mutex $mutex, string $taskId)
    {
        $this->mutex = $mutex;
        $this->taskId = $taskId;
    }

    public function __destruct()
    {
        if (! $this->released) {
            $this->mutex->forceRelease($this->taskId);
        }
    }

    /**
     * Explicitly release the lock
     */
    public function drop(): void
    {
        if (! $this->released) {
            $this->mutex->forceRelease($this->taskId);
            $this->released = true;
        }
    }
}
