<?php

declare(strict_types=1);

namespace venndev\vosaka\utils;

use Generator;
use Throwable;

/**
 * GeneratorUtil class for utility functions related to generator handling.
 *
 * This utility class provides helper methods for working with PHP generators
 * in a safe and reliable manner. It includes functions for safely extracting
 * return values from generators and handling edge cases that can occur when
 * working with generator objects in async contexts.
 *
 * The class is particularly useful in the VOsaka async runtime where generators
 * are heavily used for implementing coroutines and async operations.
 */
final class GeneratorUtil
{
    /**
     * Safely get the return value from a generator.
     *
     * Attempts to retrieve the return value from a completed generator using
     * Generator::getReturn(). If an exception occurs (such as when the generator
     * hasn't completed or doesn't have a return value), returns null instead
     * of propagating the exception.
     *
     * This method is useful for extracting return values from generators in
     * contexts where exceptions would be problematic or when you want to
     * provide a default value for generators that don't explicitly return
     * anything.
     *
     * @param Generator $value The generator to extract the return value from
     * @return mixed The generator's return value, or null if extraction fails
     */
    public static function getReturnSafe(Generator $value): mixed
    {
        try {
            return $value->getReturn();
        } catch (Throwable $e) {
            return null;
        }
    }
}
