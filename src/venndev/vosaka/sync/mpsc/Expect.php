<?php

declare(strict_types=1);

namespace venndev\vosaka\sync\mpsc;

/**
 * A utility class to check if a given input matches a specified type or condition.
 * This class provides a static method `new` that performs the type checking.
 */
final class Expect
{
    /**
     * Checks if the input matches the specified type or condition.
     *
     * This method supports:
     * - Class instance checks
     * - Callable checks
     * - Primitive type checks (int, string, float, bool, array, object, callable, resource, null)
     *
     * @param mixed $input The input value to check
     * @param mixed $type The type or condition to check against
     * @return bool Returns true if the input matches the type, false otherwise
     */
    public static function new(mixed $input, mixed $type): bool
    {
        return match (true) {
            is_object($input) &&
            is_string($type) &&
            class_exists($type) &&
            $input instanceof $type
            => true,
            is_callable($type) &&
            (fn($input, callable $check) => $check($input) === true)(
                $input,
                $type
            )
            => true,
            $input === $type => true,
            is_string($type) &&
                match ($type) {
                    "int" => is_int($input),
                    "integer" => is_int($input),
                    "string" => is_string($input),
                    "float" => is_float($input),
                    "double" => is_float($input),
                    "bool" => is_bool($input),
                    "boolean" => is_bool($input),
                    "array" => is_array($input),
                    "object" => is_object($input),
                    "callable" => is_callable($input),
                    "resource" => is_resource($input),
                    "null" => is_null($input),
                    default => false,
                }
            => true,
            default => false,
        };
    }
}
