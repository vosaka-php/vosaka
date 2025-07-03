<?php

declare(strict_types=1);

namespace venndev\vosaka\net\unix;

use Generator;
use InvalidArgumentException;
use Throwable;
use venndev\vosaka\core\Result;
use venndev\vosaka\core\Future;
use venndev\vosaka\net\NetworkConstants;
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
        $this->options = self::normalizeOptions($options);
        $this->bufferSize = $this->options["buffer_size"] ?? 8192;
        self::addToEventLoop($socket);
        self::applySocketOptions($socket, $this->options);
        VOsaka::getLoop()->addReadStream($socket, [$this, "handleRead"]);
    }

    /**
     * Handles reading data from the Unix socket.
     * This method is called by the event loop when the socket is ready for reading.
     */
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

    /**
     * Handles write operations for the Unix stream.
     * This method is called by the event loop when the socket is ready for writing.
     */
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

    /**
     * Returns the peer address of the Unix socket.
     * This is typically the path of the Unix socket file.
     * @param string $path The path of the Unix socket file.
     * @return Result<string> The peer address.
     */
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

    /**
     * Reads an exact number of bytes from the Unix socket.
     * If the connection is closed before reading the exact number of bytes,
     * it throws an exception.
     *
     * @param int $bytes The exact number of bytes to read.
     * @return Result The result containing the read data.
     */
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

    /**
     * Reads data from the Unix socket until a specified delimiter is found.
     * If the delimiter is not found before the read timeout, it throws an exception.
     *
     * @param string $delimiter The delimiter to read until.
     * @return Result<string|null> The result containing the read data or null if closed.
     */
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

                if (strlen($buffer) > NetworkConstants::UNIX_READ_BUFFER_SIZE) {
                    throw new InvalidArgumentException(
                        "Buffer size exceeded while reading until delimiter"
                    );
                }
            }
        };

        return Future::new($fn());
    }

    /**
     * Writes data to the Unix socket.
     * If the stream is closed or the write operation fails, it throws an exception.
     *
     * @param string $data The data to write to the socket.
     * @return Result<int> The number of bytes written.
     */
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

    /**
     * Returns the peer address of the Unix socket.
     * This is typically the path of the Unix socket file.
     *
     * @return string The peer address.
     */
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

    /**
     * Returns the local path of the Unix socket.
     * This is typically the path of the Unix socket file.
     *
     * @return string The local path of the Unix socket.
     */
    public function localPath(): string
    {
        return $this->path;
    }

    /**
     * Returns the options set for the Unix stream.
     *
     * @return array The options array.
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Sets the buffer size for reading and writing operations.
     * The buffer size must be greater than 0.
     *
     * @param int $size The buffer size in bytes.
     * @return UnixStream The current instance for method chaining.
     * @throws InvalidArgumentException If the size is not greater than 0.
     */
    public function setBufferSize(int $size): UnixStream
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

    /**
     * Sets the read timeout for the Unix stream.
     * The timeout must be greater than 0 seconds.
     *
     * @param int $seconds The read timeout in seconds.
     * @return UnixStream The current instance for method chaining.
     * @throws InvalidArgumentException If the timeout is not greater than 0.
     */
    public function setReadTimeout(int $seconds): UnixStream
    {
        if ($seconds <= 0) {
            throw new InvalidArgumentException(
                "Timeout must be greater than 0"
            );
        }
        $this->options["read_timeout"] = $seconds;
        return $this;
    }

    /**
     * Sets the write timeout for the Unix stream.
     * The timeout must be greater than 0 seconds.
     *
     * @param int $seconds The write timeout in seconds.
     * @return UnixStream The current instance for method chaining.
     * @throws InvalidArgumentException If the timeout is not greater than 0.
     */
    public function setWriteTimeout(int $seconds): UnixStream
    {
        if ($seconds <= 0) {
            throw new InvalidArgumentException(
                "Timeout must be greater than 0"
            );
        }
        $this->options["write_timeout"] = $seconds;
        return $this;
    }

    /**
     * Splits the Unix stream into read and write halves.
     * This allows for separate reading and writing operations on the same stream.
     *
     * @return array<UnixReadHalf|UnixWriteHalf> An array containing the read and write halves of the stream.
     */
    public function split(): array
    {
        return [new UnixReadHalf($this), new UnixWriteHalf($this)];
    }
}
