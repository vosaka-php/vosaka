<?php

declare(strict_types=1);

namespace venndev\vosaka\net;

use venndev\vosaka\core\Result;

interface ListenerInterface
{
    public static function bind(string $addr, array $options = []): Result;
    public function accept(float $timeout = 0.0): Result;
    public function localAddr(): string;
    public function getOptions(): array;
    public function isClosed(): bool;
    public function close(): void;
}
