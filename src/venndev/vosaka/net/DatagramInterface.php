<?php

declare(strict_types=1);

namespace venndev\vosaka\net;

use venndev\vosaka\core\Result;

interface DatagramInterface
{
    public function sendTo(string $data, string $addr): Result;
    public function receiveFrom(int $maxLength = 65535): Result;
    public function getLocalAddr(): string;
    public function isClosed(): bool;
    public function close(): void;
}
