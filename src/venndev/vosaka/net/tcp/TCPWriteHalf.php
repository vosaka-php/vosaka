<?php

declare(strict_types=1);

namespace venndev\vosaka\net\tcp;

use InvalidArgumentException;
use venndev\vosaka\core\Result;
use venndev\vosaka\net\StreamBase;
use venndev\vosaka\VOsaka;

final class TCPWriteHalf extends StreamBase
{
    public function __construct(
        mixed $socket,
        private readonly string $peerAddr = ""
    ) {
        $this->socket = $socket;
        $this->bufferSize = 524288;
        if ($socket) {
            self::addToEventLoop($socket);
        }
    }

    public function handleRead(): void
    {
        // No-op: Write-only stream
    }

    public function handleWrite(): void
    {
        if ($this->isClosed || empty($this->writeBuffer) || !$this->socket) {
            if ($this->writeRegistered) {
                VOsaka::getLoop()->removeWriteStream($this->socket);
                $this->writeRegistered = false;
            }
            return;
        }

        $bytesWritten = @fwrite($this->socket, $this->writeBuffer);

        if ($bytesWritten === false) {
            $this->close();
            return;
        }

        if ($bytesWritten === 0) {
            return;
        }

        $this->writeBuffer = substr($this->writeBuffer, $bytesWritten);

        if (empty($this->writeBuffer)) {
            VOsaka::getLoop()->removeWriteStream($this->socket);
            $this->writeRegistered = false;
        }
    }

    public function peerAddr(): string
    {
        return $this->peerAddr;
    }

    public function read(?int $maxBytes = null): Result
    {
        throw new InvalidArgumentException(
            "Read operation not supported on write-only stream"
        );
    }

    public function readExact(int $bytes): Result
    {
        throw new InvalidArgumentException(
            "Read operation not supported on write-only stream"
        );
    }

    public function readUntil(string $delimiter): Result
    {
        throw new InvalidArgumentException(
            "Read operation not supported on write-only stream"
        );
    }

    public function readLine(): Result
    {
        throw new InvalidArgumentException(
            "Read operation not supported on write-only stream"
        );
    }

    public function close(): void
    {
        if (!$this->isClosed && $this->socket) {
            $this->isClosed = true;
            if ($this->writeRegistered) {
                VOsaka::getLoop()->removeWriteStream($this->socket);
                $this->writeRegistered = false;
            }
        }
    }
}
