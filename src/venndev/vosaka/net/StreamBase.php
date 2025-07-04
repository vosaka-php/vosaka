<?php

declare(strict_types=1);

namespace venndev\vosaka\net;

use Generator;
use InvalidArgumentException;
use venndev\vosaka\core\Result;
use venndev\vosaka\core\Future;
use venndev\vosaka\VOsaka;

abstract class StreamBase extends SocketBase implements StreamInterface
{
    protected bool $isClosed = false;
    protected string $readBuffer = "";
    protected string $writeBuffer = "";
    protected bool $writeRegistered = false;
    protected int $bufferSize;

    public function read(?int $maxBytes = null): Result
    {
        $fn = function () use ($maxBytes): Generator {
            if ($this->isClosed) {
                throw new InvalidArgumentException("Stream is closed");
            }

            if (! empty($this->readBuffer)) {
                $bytes = $maxBytes ?? strlen($this->readBuffer);
                $data = substr($this->readBuffer, 0, $bytes);
                $this->readBuffer = substr($this->readBuffer, $bytes);
                return $data;
            }

            while (empty($this->readBuffer) && ! $this->isClosed) {
                yield;
            }

            if ($this->isClosed) {
                return "";
            }

            $bytes = $maxBytes ?? strlen($this->readBuffer);
            $data = substr($this->readBuffer, 0, $bytes);
            $this->readBuffer = substr($this->readBuffer, $bytes);
            return $data;
        };

        return Future::new($fn());
    }

    public function readExact(int $bytes): Result
    {
        $fn = function () use ($bytes): Generator {
            if ($bytes <= 0) {
                throw new InvalidArgumentException(
                    "Bytes must be greater than 0"
                );
            }

            while (strlen($this->readBuffer) < $bytes && ! $this->isClosed) {
                yield;
            }

            if ($this->isClosed) {
                throw new InvalidArgumentException("Stream closed");
            }

            $data = substr($this->readBuffer, 0, $bytes);
            $this->readBuffer = substr($this->readBuffer, $bytes);
            return $data;
        };

        return Future::new($fn());
    }

    public function readUntil(string $delimiter): Result
    {
        $fn = function () use ($delimiter): Generator {
            if (empty($delimiter)) {
                throw new InvalidArgumentException("Delimiter cannot be empty");
            }

            while (
                ($pos = strpos($this->readBuffer, $delimiter)) === false &&
                ! $this->isClosed
            ) {
                yield;
            }

            if ($this->isClosed) {
                throw new InvalidArgumentException("Stream closed");
            }

            $data = substr($this->readBuffer, 0, $pos);
            $this->readBuffer = substr(
                $this->readBuffer,
                $pos + strlen($delimiter)
            );
            return $data;
        };

        return Future::new($fn());
    }

    public function readLine(): Result
    {
        return $this->readUntil("\n");
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

            $bytesWritten = @fwrite($this->socket, $data);

            if ($bytesWritten === false) {
                $this->close();
                throw new InvalidArgumentException("Write failed");
            }

            if ($bytesWritten === strlen($data)) {
                return $bytesWritten;
            }

            $remaining = substr($data, $bytesWritten);
            $this->writeBuffer .= $remaining;

            if (! $this->writeRegistered) {
                VOsaka::getLoop()->addWriteStream($this->socket, [
                    $this,
                    "handleWrite",
                ]);
                $this->writeRegistered = true;
            }

            while (! empty($this->writeBuffer) && ! $this->isClosed) {
                yield;
            }

            return strlen($data);
        };

        return Future::new($fn());
    }

    public function writeAll(string $data): Result
    {
        return $this->write($data);
    }

    public function flush(): Result
    {
        $fn = function (): Generator {
            if ($this->isClosed) {
                throw new InvalidArgumentException("Stream is closed");
            }

            if ($this->socket && is_resource($this->socket)) {
                if (! @fflush($this->socket)) {
                    throw new InvalidArgumentException("Flush failed");
                }
            }
            yield;
        };

        return Future::new($fn());
    }

    abstract public function handleRead(): void;
    abstract public function handleWrite(): void;
    abstract public function peerAddr(): string;

    public function isClosed(): bool
    {
        return $this->isClosed || ! is_resource($this->socket);
    }

    public function close(): void
    {
        if (! $this->isClosed && $this->socket) {
            $this->isClosed = true;
            VOsaka::getLoop()->removeReadStream($this->socket);
            if ($this->writeRegistered) {
                VOsaka::getLoop()->removeWriteStream($this->socket);
                $this->writeRegistered = false;
            }
            self::removeFromEventLoop($this->socket);
            $this->socket = null;
        }
    }
}
