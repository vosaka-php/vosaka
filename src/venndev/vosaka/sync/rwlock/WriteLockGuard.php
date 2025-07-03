<?php

declare(strict_types=1);

namespace venndev\vosaka\sync\rwlock;

use venndev\vosaka\sync\RwLock;

/**
 * Write Lock Guard - automatically releases write lock when destroyed
 */
final class WriteLockGuard
{
    private ?RwLock $lock;

    public function __construct(RwLock $lock)
    {
        $this->lock = $lock;
    }

    public function __destruct()
    {
        $this->release();
    }

    /**
     * Create a new WriteLockGuard instance
     *
     * @param RwLock $lock The RwLock instance to guard
     * @return self
     */
    public static function new(RwLock $lock): WriteLockGuard
    {
        return new self($lock);
    }

    /**
     * Manually release the write lock
     */
    public function release(): void
    {
        if ($this->lock !== null) {
            $this->lock->releaseWrite();
            $this->lock = null;
        }
    }

    /**
     * Check if the lock is still held
     */
    public function isHeld(): bool
    {
        return $this->lock !== null;
    }
}