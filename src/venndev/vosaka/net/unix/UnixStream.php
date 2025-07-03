<?php

declare(strict_types=1);

namespace venndev\vosaka\net\unix;

use Generator;
use InvalidArgumentException;
use Throwable;
use venndev\vosaka\core\Result;
use venndev\vosaka\core\Future;
use venndev\vosaka\net\StreamBase;
use venndev\vosaka\VOsaka;

final class UnixStream extends StreamBase
{
    public function __construct(
        mixed $socket,
        private readonly string $path,
        array $options = []
    ) {
        $this->socket = $socket;
        $this->options = array_merge(
            [
                "buffer_size" => 8192,
                "read_timeout" => 30,
                "write_timeout" => 30,
                "keepalive" => true,
                "linger" => false,
                "sndbuf" => 65536,
                "rcvbuf" => 65536,
            ],
            $options
        );
        $this->bufferSize = $this->options["buffer_size"];
        self::addToEventLoop($socket);
        self::applySocketOptions($socket, $this->options);
        VOsaka::getLoop()->addReadStream($socket, [$this, "handleRead"]);
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

    public function read(?int $maxBytes = null): Result
    {
        $fn = function () use ($maxBytes): Generator {
            if ($this->isClosed) {
                throw new InvalidArgumentException("Stream is closed");
            }

            $maxBytes ??= $this->bufferSize;
            $startTime = time();

            while (true) {
                if (time() - $startTime > $this->options["read_timeout"]) {
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

            while ($remaining > 0) {
                if (time() - $startTime > $this->options["read_timeout"]) {
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

            while (true) {
                if (time() - $startTime > $this->options["read_timeout"]) {
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

            while ($bytesWritten < $totalBytes) {
                if (time() - $startTime > $this->options["write_timeout"]) {
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

    public function peerAddr(): string
    {
        if (!$this->socket || $this->isClosed) {
            return "";
        }

        try {
            return stream_socket_get_name($this->socket, true) ?: "";
        } catch (Throwable) {
            return "";
        }
    }

    public function localPath(): string
    {
        return $this->path;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setBufferSize(int $size): self
    {
        if ($size <= 0) {
            throw new InvalidArgumentException(
                "Buffer size must be greater than 0"
            );
        }
        $this->bufferSize = $size;
        $this->options["buffer_size"] = $size;
        return $this;
    }

    public function setReadTimeout(int $seconds): self
    {
        if ($seconds <= 0) {
            throw new InvalidArgumentException(
                "Timeout must be greater than 0"
            );
        }
        $this->options["read_timeout"] = $seconds;
        return $this;
    }

    public function setWriteTimeout(int $seconds): self
    {
        if ($seconds <= 0) {
            throw new InvalidArgumentException(
                "Timeout must be greater than 0"
            );
        }
        $this->options["write_timeout"] = $seconds;
        return $this;
    }

    public function split(): array
    {
        return [new UnixReadHalf($this), new UnixWriteHalf($this)];
    }
}
