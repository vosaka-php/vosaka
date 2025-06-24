<?php

declare(strict_types=1);

namespace venndev\vosaka\utils;

use Generator;
use Throwable;

final class GeneratorUtil
{
    public static function getReturnSafe(Generator $value): mixed
    {
        try {
            return $value->getReturn();
        } catch (Throwable $e) {
            return null;
        }
    }
}