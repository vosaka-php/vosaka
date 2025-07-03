<?php

declare(strict_types=1);

namespace venndev\vosaka\net\unix;

use Generator;
use InvalidArgumentException;
use venndev\vosaka\core\Result;
use venndev\vosaka\core\Future;
use venndev\vosaka\net\StreamBase;
use venndev\vosaka\VOsaka;

final class UnixWriteHalf extends StreamBase
{
    public function __construct(private readonly UnixStream $stream)
    {
        $this->socket = $stream->socket;
        $this->bufferSize = $stream->getOptions()["buffer_size"];
        if ($this->socket) {
            $this->addToEventLoop($this->socket);
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
        return $this->stream->peerAddr();
    }

    public function write(string $data): Result
    {
        $fn = function () use ($data): Generator {
            if ($this->isClosed) {
                throw new InvalidArgumentException("Stream is closed");
            }

            if (empty($data)) {
                return 0;
            }

            $totalBytes = strlen($data);
            $bytesWritten = 0;
            $startTime = time();
            $timeout = $this->stream->getOptions()["write_timeout"];

            while ($bytesWritten < $totalBytes) {
                if (time() - $startTime > $timeout) {
                    throw new InvalidArgumentException(
                        "Write timeout exceeded"
                    );
                }

                $chunk = substr($data, $bytesWritten);
                $result = @fwrite($this->socket, $chunk);

                if ($result === false) {
                    if (!is_resource($this->socket) || feof($this->socket)) {
                        throw new InvalidArgumentException(
                            "Connection closed during write"
                        );
                    }
                    throw new InvalidArgumentException("Write failed");
                }

                if ($result === 0) {
                    yield;
                    continue;
                }

                $bytesWritten += $result;

                if ($bytesWritten < $totalBytes) {
                    yield;
                }
            }

            return $bytesWritten;
        };

        return Future::new($fn());
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
