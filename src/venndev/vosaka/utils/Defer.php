<?php

declare(strict_types=1);

namespace venndev\vosaka\utils;

use Closure;
use Generator;
use InvalidArgumentException;

/**
 * Defer class for handling deferred execution of callbacks in the event loop.
 *
 * This class allows you to schedule callbacks to be executed later, typically
 * used for cleanup operations or tasks that should run after the current
 * task completes. Supports callables, Closures, and Generators.
 */
final class Defer
{
    /**
     * Constructor for Defer instruction.
     *
     * @param mixed $callback The callback to defer (callable, Closure, or Generator)
     * @throws InvalidArgumentException If the callback is not a valid type
     */
    public function __construct(public mixed $callback)
    {
        if (
            !(
                is_callable($callback) ||
                $callback instanceof Closure ||
                $callback instanceof Generator
            )
        ) {
            throw new InvalidArgumentException(
                "DeferInstruction requires a callable, Closure, or Generator as its argument." .
                    " Received: " .
                    gettype($callback) .
                    "."
            );
        }
    }

    /**
     * Create a Defer instance with the specified callback.
     *
     * This is a factory method that provides a convenient way to create
     * Defer instances. The 'c' stands for 'create'.
     *
     * @param callable $callback The callback to defer for later execution
     * @return Defer A new Defer instance
     */
    public static function c(callable $callback): Defer
    {
        return new Defer($callback);
    }
}
