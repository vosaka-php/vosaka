<?php

declare(strict_types=1);

namespace venndev\vosaka\utils;

use Generator;

final class CallableUtil
{

    public static function toGenerator(callable $callable): Generator
    {
        yield $callable();
    }

    public static function makeAllToCallable(mixed $value): callable
    {
        if (is_callable($value)) {
            return $value;
        }

        if ($value instanceof Generator) {
            return fn(): Generator => $value;
        }

        return fn() => $value;
    }
}