<?php

declare(strict_types=1);

namespace venndev\vosaka\core;

use RuntimeException;
use venndev\vosaka\core\interfaces\Option;

final class None extends Option
{
    public function isSome(): bool
    {
        return false;
    }

    public function isNone(): bool
    {
        return true;
    }

    public function unwrap()
    {
        throw new RuntimeException("Called unwrap on None");
    }

    public function unwrapOr($default)
    {
        return $default;
    }
}
