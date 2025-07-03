<?php

declare(strict_types=1);

namespace venndev\vosaka\core;

use RuntimeException;
use venndev\vosaka\core\interfaces\ResultType;

final class Err extends ResultType
{
    private $error;

    public function __construct($error)
    {
        $this->error = $error;
    }

    public function isOk(): bool
    {
        return false;
    }

    public function isErr(): bool
    {
        return true;
    }

    public function unwrap()
    {
        throw new RuntimeException("Called unwrap on Err: {$this->error}");
    }

    public function unwrapOr($default)
    {
        return $default;
    }
}
