<?php

declare(strict_types=1);

namespace venndev\vosaka\sync;

use Generator;
use InvalidArgumentException;

/**
 * Semaphore class for controlling access to shared resources in async contexts.
 *
 * A semaphore is a synchronization primitive that maintains a count of permits
 * available for concurrent access to a shared resource. Tasks must acquire a
 * permit before accessing the resource and release it when done.
 *
 * This implementation is designed for use with VOsaka's async runtime and
 * uses yielding to avoid blocking the event loop when waiting for permits.
 */
final class Semaphore
{
    private int $count;
    private int $maxCount;

    /**
     * Constructor for Semaphore.
     *
     * Creates a new semaphore with the specified maximum number of permits.
     * The semaphore starts with zero permits acquired, meaning all permits
     * are initially available.
     *
     * @param int $maxCount Maximum number of permits available (must be positive)
     * @throws InvalidArgumentException If maxCount is not positive
     */
    public function __construct(int $maxCount)
    {
        if ($maxCount <= 0) {
            throw new InvalidArgumentException(
                "Semaphore count must be greater than zero."
            );
        }
        $this->maxCount = $maxCount;
        $this->count = 0;
    }

    /**
     * Acquire a permit from the semaphore.
     *
     * Attempts to acquire a permit from the semaphore. If no permits are
     * available (count has reached maxCount), the method will yield control
     * to the event loop and keep trying until a permit becomes available.
     *
     * This method is async-safe and will not block the event loop, allowing
     * other tasks to run while waiting for a permit.
     *
     * @return Generator Yields until a permit is acquired
     */
    public function acquire(): Generator
    {
        while ($this->count >= $this->maxCount) {
            yield;
        }

        $this->count++;
    }

    /**
     * Release a permit back to the semaphore.
     *
     * Releases a previously acquired permit, making it available for other
     * tasks to acquire. This decrements the current count of acquired permits.
     * If no permits are currently acquired, this method has no effect.
     *
     * Always call release() after you're done with the protected resource
     * to ensure other tasks can access it.
     *
     * @return void
     */
    public function release(): void
    {
        if ($this->count > 0) {
            $this->count--;
        }
    }

    /**
     * Get the current number of acquired permits.
     *
     * Returns the number of permits currently acquired by tasks. This value
     * will be between 0 and maxCount. The number of available permits is
     * (maxCount - current count).
     *
     * @return int The number of permits currently acquired
     */
    public function getCount(): int
    {
        return $this->count;
    }
}
