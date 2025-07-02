<?php

declare(strict_types=1);

namespace venndev\vosaka\core;

use Generator;

/**
 * Future class for creating Result and Option instances
 */
final class Future
{
    /**
     * Create a new Result instance wrapping a Generator task
     *
     * @param Generator $task The generator task to wrap
     * @return Result The Result instance
     */
    public static function new(Generator $task): Result
    {
        return new Result($task);
    }

    /**
     * Create a successful Result with a value
     *
     * @param mixed $value The success value
     * @return Ok The successful result
     */
    public static function ok(mixed $value): Ok
    {
        return new Ok($value);
    }

    /**
     * Create an error Result with an error
     *
     * @param mixed $error The error value
     * @return Err The error result
     */
    public static function err(mixed $error): Err
    {
        return new Err($error);
    }

    /**
     * Create an Option with a value
     *
     * @param mixed $value The value to wrap
     * @return Some The Some option
     */
    public static function some(mixed $value): Some
    {
        return new Some($value);
    }

    /**
     * Create an empty Option
     *
     * @return None The None option
     */
    public static function none(): None
    {
        return new None();
    }
}