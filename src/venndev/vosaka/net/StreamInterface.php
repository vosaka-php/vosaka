<?php

declare(strict_types=1);

namespace venndev\vosaka\net;

use venndev\vosaka\core\Result;

interface StreamInterface
{
    public function read(?int $maxBytes = null): Result;
    public function readExact(int $bytes): Result;
    public function readUntil(string $delimiter): Result;
    public function readLine(): Result;
    public function write(string $data): Result;
    public function writeAll(string $data): Result;
    public function flush(): Result;
    public function peerAddr(): string;
    public function isClosed(): bool;
    public function close(): void;
}
