<?php

declare(strict_types=1);

namespace venndev\vosaka\core\interfaces;

abstract class ResultType
{
    abstract public function isOk(): bool;
    abstract public function isErr(): bool;
    abstract public function unwrap();
    abstract public function unwrapOr($default);
}