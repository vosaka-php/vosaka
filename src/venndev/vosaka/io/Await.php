<?php

declare(strict_types=1);

namespace venndev\vosaka\io;

use Closure;
use Generator;
use Throwable;
use venndev\vosaka\utils\Result;

final class Await
{
    public static function c(Generator|Closure $task): Result
    {
        $fn = function () use ($task): Generator {
            try {
                if ($task instanceof Closure) {
                    $task = fn() => yield $task();
                }

                if ($task instanceof Generator) {
                    yield from $task;
                }

                return $task->getReturn();
            } catch (Throwable $e) {
                return $e;
            }
        };

        $result = new Result($fn());
        return $result;
    }
}