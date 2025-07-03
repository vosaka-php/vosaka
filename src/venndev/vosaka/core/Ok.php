<?php

declare(strict_types=1);

namespace venndev\vosaka\core;

use venndev\vosaka\core\interfaces\ResultType;

final class Ok extends ResultType
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function isOk(): bool
    {
        return true;
    }

    public function isErr(): bool
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
