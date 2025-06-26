<?php

declare(strict_types=1);

namespace venndev\vosaka\time;

/**
 * Interval class for handling recurring asynchronous intervals in the event loop.
 *
 * This class provides various methods to create interval instructions with different
 * time units (seconds, milliseconds, microseconds) that can be yielded in generators
 * to create recurring delays without blocking the event loop. Unlike Sleep which
 * creates a one-time delay, Interval is designed for recurring operations.
 *
 * The class implements the Time interface and can be used anywhere a time-based
 * instruction is expected in the VOsaka async runtime.
 */
final class Interval implements \venndev\vosaka\core\interfaces\Time
{
    /**
     * Constructor for Interval instruction.
     *
     * @param float $seconds The interval duration in seconds (can be fractional)
     */
    public function __construct(public float $seconds)
    {
        // TODO: Implement the logic for handling interval instructions.
    }

    /**
     * Create an Interval instance with the specified number of seconds.
     *
     * This is a factory method that provides a convenient way to create
     * Interval instances. The 'c' stands for 'create'.
     *
     * @param float $seconds The interval duration in seconds (can be fractional)
     * @return self A new Interval instance
     */
    public static function c(float $seconds): self
    {
        return new self($seconds);
    }

    /**
     * Create an Interval instance with the specified number of milliseconds.
     *
     * Converts milliseconds to seconds for internal storage.
     *
     * @param int $milliseconds The interval duration in milliseconds
     * @return self A new Interval instance
     */
    public static function ms(int $milliseconds): self
    {
        return new self($milliseconds / 1000.0); // Convert milliseconds to seconds
    }

    /**
     * Create an Interval instance with the specified number of microseconds.
     *
     * Converts microseconds to seconds for internal storage.
     *
     * @param int $microseconds The interval duration in microseconds
     * @return self A new Interval instance
     */
    public static function us(int $microseconds): self
    {
        return new self($microseconds / 1_000_000.0); // Convert microseconds to seconds
    }

    /**
     * Create an Interval instance with a default tick duration.
     *
     * Creates an interval with a duration of 1 millisecond, which is useful
     * for high-frequency recurring operations or for yielding control to the
     * event loop very frequently without significant delay.
     *
     * @return self A new Interval instance with 1ms duration
     */
    public static function tick(): self
    {
        return new self(0.001); // 1 millisecond
    }
}
