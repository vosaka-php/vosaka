<?php

declare(strict_types=1);

namespace venndev\vosaka\sync\rwlock;

use venndev\vosaka\sync\RwLock;

/**
 * Read Lock Guard - automatically releases read lock when destroyed
 */
final class ReadLockGuard
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
     * Create a new ReadLockGuard instance
     *
     * @param RwLock $lock The RwLock instance to guard
     * @return ReadLockGuard
     */
    public static function new(RwLock $lock): ReadLockGuard
    {
        return new self($lock);
    }

    /**
     * Manually release the read lock
     */
    public function release(): void
    {
        if ($this->lock !== null) {
            $this->lock->releaseRead();
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
