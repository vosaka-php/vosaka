<?php

declare(strict_types=1);

namespace venndev\vosaka\net\unix;

use Generator;
use InvalidArgumentException;
use venndev\vosaka\core\Result;
use venndev\vosaka\core\Future;
use venndev\vosaka\net\StreamBase;
use venndev\vosaka\VOsaka;

final class UnixReadHalf extends StreamBase
{
    public function __construct(private readonly UnixStream $stream)
    {
        $this->socket = $stream->socket;
        $this->bufferSize = $stream->getOptions()["buffer_size"];
        if ($this->socket) {
            self::addToEventLoop($this->socket);
            VOsaka::getLoop()->addReadStream($this->socket, [
                $this,
                "handleRead",
            ]);
        }
    }

    public function handleRead(): void
    {
        if ($this->isClosed || !$this->socket) {
            return;
        }

        $data = @fread($this->socket, $this->bufferSize);

        if ($data === false || ($data === "" && feof($this->socket))) {
            $this->close();
            return;
        }

        $this->readBuffer .= $data;
    }

    public function handleWrite(): void
    {
        // No-op: Read-only stream
    }

    public function peerAddr(): string
    {
        return $this->stream->peerAddr();
    }

    public function read(?int $maxBytes = null): Result
    {
        $fn = function () use ($maxBytes): Generator {
            if ($this->isClosed) {
                throw new InvalidArgumentException("Stream is closed");
            }

            $maxBytes ??= $this->bufferSize;
            $startTime = time();
            $timeout = $this->stream->getOptions()["read_timeout"];

            while (true) {
                if (time() - $startTime > $timeout) {
                    throw new InvalidArgumentException("Read timeout exceeded");
                }

                $data = @fread($this->socket, $maxBytes);

                if ($data === false || ($data === "" && feof($this->socket))) {
                    $this->close();
                    return null;
                }

                if ($data !== "") {
                    return $data;
                }

                yield;
            }
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

            $buffer = "";
            $remaining = $bytes;
            $startTime = time();
            $timeout = $this->stream->getOptions()["read_timeout"];

            while ($remaining > 0) {
                if (time() - $startTime > $timeout) {
                    throw new InvalidArgumentException("Read timeout exceeded");
                }

                $chunk = yield from $this->read(
                    min($remaining, $this->bufferSize)
                )->unwrap();

                if ($chunk === null) {
                    throw new InvalidArgumentException(
                        "Connection closed before reading exact bytes (got " .
                            strlen($buffer) .
                            " of {$bytes} bytes)"
                    );
                }

                $buffer .= $chunk;
                $remaining -= strlen($chunk);
            }

            return $buffer;
        };

        return Future::new($fn());
    }

    public function readUntil(string $delimiter): Result
    {
        $fn = function () use ($delimiter): Generator {
            if (empty($delimiter)) {
                throw new InvalidArgumentException("Delimiter cannot be empty");
            }

            $buffer = "";
            $delimiterLength = strlen($delimiter);
            $startTime = time();
            $timeout = $this->stream->getOptions()["read_timeout"];

            while (true) {
                if (time() - $startTime > $timeout) {
                    throw new InvalidArgumentException("Read timeout exceeded");
                }

                $chunk = yield from $this->read(1)->unwrap();

                if ($chunk === null) {
                    return $buffer ?: null;
                }

                $buffer .= $chunk;

                if (
                    strlen($buffer) >= $delimiterLength &&
                    substr($buffer, -$delimiterLength) === $delimiter
                ) {
                    return substr($buffer, 0, -$delimiterLength);
                }

                if (strlen($buffer) > 1048576) {
                    throw new InvalidArgumentException(
                        "Buffer size exceeded while reading until delimiter"
                    );
                }
            }
        };

        return Future::new($fn());
    }

    public function write(string $data): Result
    {
        throw new InvalidArgumentException(
            "Write operation not supported on read-only stream"
        );
    }

    public function writeAll(string $data): Result
    {
        throw new InvalidArgumentException(
            "Write operation not supported on read-only stream"
        );
    }

    public function flush(): Result
    {
        throw new InvalidArgumentException(
            "Flush operation not supported on read-only stream"
        );
    }

    public function close(): void
    {
        if (!$this->isClosed && $this->socket) {
            $this->isClosed = true;
            VOsaka::getLoop()->removeReadStream($this->socket);
        }
    }
}
