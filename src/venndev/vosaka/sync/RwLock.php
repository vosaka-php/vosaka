<?php

declare(strict_types=1);

namespace venndev\vosaka\sync;

use Generator;
use RuntimeException;
use SplQueue;
use venndev\vosaka\core\Future;
use venndev\vosaka\core\Result;
use venndev\vosaka\sync\rwlock\ReadLockGuard;
use venndev\vosaka\sync\rwlock\WriteLockGuard;

/**
 * RwLock - Reader-Writer Lock implementation using Generator
 *
 * This class provides a Reader-Writer lock mechanism that allows:
 * - Multiple readers to access the resource simultaneously
 * - Only one writer to access the resource at a time
 * - Writers have priority over readers to prevent writer starvation
 *
 * The implementation uses Generator-based coroutines to provide
 * non-blocking asynchronous locking behavior.
 */
final class RwLock
{
    private int $readerCount = 0;
    private bool $writerActive = false;
    private SplQueue $readerQueue;
    private SplQueue $writerQueue;
    private int $waitingWriters = 0;

    public function __construct()
    {
        $this->readerQueue = new SplQueue();
        $this->writerQueue = new SplQueue();
    }

    /**
     * Create a new instance of RwLock
     *
     * This method is used to create a new RwLock instance.
     *
     * @return RwLock
     */
    public static function new(): RwLock
    {
        return new self();
    }

    /**
     * Acquire a read lock
     *
     * Allows multiple readers to acquire the lock simultaneously unless
     * there are waiting writers (to prevent writer starvation).
     *
     * @return Generator<mixed, mixed, mixed, ReadLockGuard>
     */
    public function read(): Generator
    {
        // Wait if there's an active writer or waiting writers
        while ($this->writerActive || $this->waitingWriters > 0) {
            $resolver = null;
            $promise = new class ($resolver) {
                private $resolver;
                public function __construct(&$resolver)
                {
                    $this->resolver = &$resolver;
                }
                public function resolve(): void
                {
                    if ($this->resolver) {
                        ($this->resolver)();
                    }
                }
            };

            $this->readerQueue->enqueue($promise);

            yield from $this->waitForResolution($promise);
        }

        $this->readerCount++;
        return new ReadLockGuard($this);
    }

    /**
     * Acquire a write lock
     *
     * Only one writer can hold the lock at a time, and writers have
     * priority over readers to prevent starvation.
     *
     * @return Generator<mixed, mixed, mixed, WriteLockGuard>
     */
    public function write(): Generator
    {
        $this->waitingWriters++;

        // Wait while there are active readers or an active writer
        while ($this->readerCount > 0 || $this->writerActive) {
            $resolver = null;
            $promise = new class ($resolver) {
                private $resolver;
                public function __construct(&$resolver)
                {
                    $this->resolver = &$resolver;
                }
                public function resolve(): void
                {
                    if ($this->resolver) {
                        ($this->resolver)();
                    }
                }
            };

            $this->writerQueue->enqueue($promise);

            yield from $this->waitForResolution($promise);
        }

        $this->waitingWriters--;
        $this->writerActive = true;
        return new WriteLockGuard($this);
    }

    /**
     * Try to acquire a read lock without blocking
     *
     * @return Result<ReadLockGuard>
     */
    public function tryRead(): Result
    {
        $fn = function (): Generator {
            yield;
            if ($this->writerActive || $this->waitingWriters > 0) {
                return Future::err(
                    "Read lock unavailable: writer active or waiting"
                );
            }

            $this->readerCount++;
            return Future::ok(new ReadLockGuard($this));
        };

        return Future::new($fn());
    }

    /**
     * Try to acquire a write lock without blocking
     *
     * @return Result<WriteLockGuard>
     */
    public function tryWrite(): Result
    {
        $fn = function (): Generator {
            yield;
            if ($this->readerCount > 0 || $this->writerActive) {
                return Future::err(
                    "Write lock unavailable: readers active or writer active"
                );
            }

            $this->writerActive = true;
            return Future::ok(new WriteLockGuard($this));
        };

        return Future::new($fn());
    }

    /**
     * Release a read lock (internal use)
     */
    public function releaseRead(): void
    {
        if ($this->readerCount <= 0) {
            throw new RuntimeException(
                "Attempting to release read lock when no readers active"
            );
        }

        $this->readerCount--;

        if ($this->readerCount === 0 && !$this->writerQueue->isEmpty()) {
            $promise = $this->writerQueue->dequeue();
            $promise->resolve();
        }
    }

    /**
     * Release a write lock (internal use)
     */
    public function releaseWrite(): void
    {
        if (!$this->writerActive) {
            throw new RuntimeException(
                "Attempting to release write lock when no writer active"
            );
        }

        $this->writerActive = false;

        if (!$this->writerQueue->isEmpty()) {
            $promise = $this->writerQueue->dequeue();
            $promise->resolve();
        } elseif (!$this->readerQueue->isEmpty()) {
            while (!$this->readerQueue->isEmpty()) {
                $promise = $this->readerQueue->dequeue();
                $promise->resolve();
            }
        }
    }

    /**
     * Get current lock status
     */
    public function getStatus(): array
    {
        return [
            "reader_count" => $this->readerCount,
            "writer_active" => $this->writerActive,
            "waiting_writers" => $this->waitingWriters,
            "waiting_readers" => $this->readerQueue->count(),
        ];
    }

    /**
     * Wait for promise resolution (simplified implementation)
     */
    private function waitForResolution($promise): Generator
    {
        // Simple yield-based waiting mechanism
        // In a real async environment, this would integrate with the event loop
        $maxAttempts = 1000;
        $attempts = 0;

        while ($attempts < $maxAttempts) {
            yield;
            $attempts++;

            // Check if we can proceed (simplified check)
            if (!$this->writerActive && $this->waitingWriters === 0) {
                break;
            }

            if (!$this->writerActive && $this->readerCount === 0) {
                break;
            }
        }
    }
}
