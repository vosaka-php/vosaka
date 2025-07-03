<?php

declare(strict_types=1);

namespace venndev\vosaka\core;

use venndev\vosaka\core\interfaces\Option;

final class Some extends Option
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function isSome(): bool
    {
        return true;
    }
    public function isNone(): bool
    {
        return false;
    }
    public function unwrap()
    {
        return $this->value;
    }
    public function unwrapOr($default)
    {
        return $this->value;
    }
}
