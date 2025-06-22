<?php

declare(strict_types=1);

namespace venndev\vosaka\eventloop\scheduler;

use Closure;
use Generator;
use InvalidArgumentException;
use venndev\vosaka\utils\CallableUtil;

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

    public static function c(mixed $callback = null): Defer
    {
        $callback = CallableUtil::makeAllToCallable($callback);
        return new Defer($callback);
    }
}