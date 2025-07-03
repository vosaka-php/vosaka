<?php

declare(strict_types=1);

namespace venndev\vosaka\sync;

use Exception;
use Generator;
use venndev\vosaka\core\Future;
use venndev\vosaka\core\interfaces\Option;
use venndev\vosaka\core\interfaces\ResultType;

/**
 * Returns Result<MutexGuard, Error> for lock operations
 * Uses RAII-style MutexGuard for automatic cleanup
 * Provides try_lock() that returns Option<MutexGuard>
 * Uses unwrap() and expect() for error handling
 */
final class Mutex
{
    private bool $locked = false;
    private ?string $owner = null;
    private array $waitingQueue = [];
    private int $waitingCount = 0;

    /**
     * Create a new Mutex
     */
    public static function new(): self
    {
        return new self();
    }

    /**
     * Lock the mutex and return a MutexGuard
     *
     * @param string|null $taskId Optional task identifier
     * @return Generator<ResultType>
     */
    public function lock(?string $taskId = null): Generator
    {
        $taskId ??= $this->generateTaskId();

        try {
            // Add to waiting queue for fairness
            $this->waitingQueue[] = $taskId;
            $this->waitingCount++;

            while ($this->locked || $this->waitingQueue[0] !== $taskId) {
                yield;
            }

            // Remove from queue and acquire lock
            array_shift($this->waitingQueue);
            $this->waitingCount--;
            $this->locked = true;
            $this->owner = $taskId;

            return Future::ok(new MutexGuard($this, $taskId));

        } catch (Exception $e) {
            $this->removeFromQueue($taskId);
            return Future::err($e->getMessage());
        }
    }

    /**
     * Try to lock the mutex without waiting
     *
     * @param string|null $taskId Optional task identifier
     * @return Option
     */
    public function tryLock(?string $taskId = null): Option
    {
        if ($this->locked) {
            return Future::none();
        }

        $taskId = $taskId ?? $this->generateTaskId();
        $this->locked = true;
        $this->owner = $taskId;

        return Future::some(new MutexGuard($this, $taskId));
    }

    /**
     * Lock with timeout
     *
     * @param float $timeoutSeconds Maximum time to wait
     * @param string|null $taskId Optional task identifier
     * @return Generator<ResultType>
     */
    public function lockTimeout(float $timeoutSeconds, ?string $taskId = null): Generator
    {
        $taskId ??= $this->generateTaskId();
        $startTime = microtime(true);

        try {
            $this->waitingQueue[] = $taskId;
            $this->waitingCount++;

            while ($this->locked || $this->waitingQueue[0] !== $taskId) {
                if ((microtime(true) - $startTime) >= $timeoutSeconds) {
                    $this->removeFromQueue($taskId);
                    return Future::err("Timeout after {$timeoutSeconds} seconds");
                }
                yield;
            }

            // Remove from queue and acquire lock
            array_shift($this->waitingQueue);
            $this->waitingCount--;
            $this->locked = true;
            $this->owner = $taskId;

            return Future::ok(new MutexGuard($this, $taskId));

        } catch (Exception $e) {
            $this->removeFromQueue($taskId);
            return Future::err($e->getMessage());
        }
    }

    /**
     * Internal method to force release (called by MutexGuard)
     */
    public function forceRelease(string $taskId): void
    {
        if (! $this->locked) {
            return;
        }

        if ($this->owner !== $taskId) {
            trigger_error(
                "Mutex released by task '{$taskId}' but owned by '{$this->owner}'",
                E_USER_WARNING
            );
        }

        $this->locked = false;
        $this->owner = null;
    }

    /**
     * Check if mutex is locked
     */
    public function isLocked(): bool
    {
        return $this->locked;
    }

    /**
     * Get current owner as Option<string>
     */
    public function owner(): Option
    {
        return $this->owner !== null ? Future::some($this->owner) : Future::none();
    }

    /**
     * Get waiting count
     */
    public function waitingCount(): int
    {
        return $this->waitingCount;
    }

    private function generateTaskId(): string
    {
        return 'task_'.uniqid().'_'.random_int(1000, 9999);
    }

    private function removeFromQueue(string $taskId): void
    {
        $key = array_search($taskId, $this->waitingQueue);
        if ($key !== false) {
            unset($this->waitingQueue[$key]);
            $this->waitingQueue = array_values($this->waitingQueue);
            $this->waitingCount--;
        }
    }
}