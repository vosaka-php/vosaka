<?php

declare(strict_types=1);

namespace venndev\vosaka\time;

use Generator;

/**
 * Sleep class for handling asynchronous sleep operations in the event loop.
 *
 * This class provides various methods to create sleep instructions with different
 * time units (seconds, milliseconds, microseconds) that can be yielded in generators
 * to pause execution without blocking the event loop.
 */
final class Sleep implements \venndev\vosaka\core\interfaces\Time
{
    /**
     * Constructor for Sleep instruction.
     *
     * @param float $seconds The number of seconds to sleep (can be fractional)
     */
    public function __construct(public float $seconds)
    {
        // TODO: Implement the logic for handling sleep instructions.
    }

    /**
     * Create a Sleep instance with the specified number of seconds.
     *
     * This is a factory method that provides a convenient way to create
     * Sleep instances. The 'c' stands for 'create'.
     *
     * @param float $seconds The number of seconds to sleep (can be fractional)
     * @return self A new Sleep instance
     */
    public static function c(float $seconds): self
    {
        return new self($seconds);
    }

    /**
     * Create a Sleep instance with the specified number of milliseconds.
     *
     * Converts milliseconds to seconds for internal storage.
     *
     * @param int $milliseconds The number of milliseconds to sleep
     * @return self A new Sleep instance
     */
    public static function ms(int $milliseconds): self
    {
        return new self($milliseconds / 1000.0);
    }

    /**
     * Create a Sleep instance with the specified number of microseconds.
     *
     * Converts microseconds to seconds for internal storage.
     *
     * @param int $microseconds The number of microseconds to sleep
     * @return self A new Sleep instance
     */
    public static function us(int $microseconds): self
    {
        return new self($microseconds / 1_000_000.0);
    }

    /**
     * Convert the sleep instruction to a generator.
     *
     * This method yields control back to the event loop for the specified
     * duration, allowing other tasks to run while waiting.
     *
     * @return Generator A generator that yields until the sleep duration is complete
     */
    public function toGenerator(): Generator
    {
        $time = microtime(true) + $this->seconds;
        while (microtime(true) < $time) {
            yield;
        }
    }
}
