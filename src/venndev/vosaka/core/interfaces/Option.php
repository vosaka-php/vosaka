<?php

declare(strict_types=1);

namespace venndev\vosaka\core\interfaces;

/**
 * Option type similar to
 */
abstract class Option
{
    abstract public function isSome(): bool;
    abstract public function isNone(): bool;
    abstract public function unwrap();
    abstract public function unwrapOr($default);
}