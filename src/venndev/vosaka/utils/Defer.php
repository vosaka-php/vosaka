<?php

declare(strict_types=1);

namespace venndev\vosaka\utils;

use Closure;
use Generator;
use InvalidArgumentException;

final class Defer
{
    public function __construct(public mixed $callback)
    {
        if (
            !(is_callable($callback) ||
                $callback instanceof Closure ||
                $callback instanceof Generator)
        ) {
            throw new InvalidArgumentException(
                'DeferInstruction requires a callable, Closure, or Generator as its argument.' .
                ' Received: ' . gettype($callback) . '.'
            );
        }
    }

    public static function c(callable $callback): Defer
    {
        return new Defer($callback);
    }
}