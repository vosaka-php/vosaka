<?php

declare(strict_types=1);

namespace venndev\vosaka\net\unix;

use Generator;
use InvalidArgumentException;
use venndev\vosaka\core\Result;
use venndev\vosaka\core\Future;
use venndev\vosaka\net\NetworkConstants;
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
     * Handles write operations for the Unix read half.
     * This is a no-op since this is a read-only stream.
     */
    public function handleWrite(): void
    {
        // No-op: Read-only stream
    }

    /**
     * Returns the peer address of the Unix socket.
     * This is typically the path of the Unix socket file.
     *
     * @return string The peer address.
     */
    public function peerAddr(): string
    {
        return $this->stream->peerAddr();
    }

    /**
     * Reads data from the stream.
     * If no maxBytes is specified, it reads up to the buffer size.
     *
     * @param int|null $maxBytes Maximum number of bytes to read.
     * @return Result The result containing the read data or null if closed.
     */
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

    /**
     * Reads an exact number of bytes from the stream.
     * If the stream is closed before reading the exact bytes, an exception is thrown.
     *
     * @param int $bytes Number of bytes to read.
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

    /**
     * Reads data from the stream until a specific delimiter is encountered.
     * If the delimiter is not found before the read timeout, an exception is thrown.
     *
     * @param string $delimiter The delimiter to read until.
     * @return Result The result containing the read data up to the delimiter.
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

                if (strlen($buffer) > NetworkConstants::READ_BUFFER_SIZE) {
                    throw new InvalidArgumentException(
                        "Buffer size exceeded while reading until delimiter"
                    );
                }
            }
        };

        return Future::new($fn());
    }

    /**
     * Reads a single line from the stream.
     * This method reads until a newline character is encountered.
     *
     * @return Result The result containing the read line or null if closed.
     */
    public function write(string $data): Result
    {
        throw new InvalidArgumentException(
            "Write operation not supported on read-only stream"
        );
    }

    /**
     * Writes all data to the stream.
     * This method is asynchronous and returns a Result object.
     *
     * @param string $data The data to write.
     * @return Result The result of the write operation.
     */
    public function writeAll(string $data): Result
    {
        throw new InvalidArgumentException(
            "Write operation not supported on read-only stream"
        );
    }

    /**
     * Writes data until the stream is closed.
     * This method is not supported for read-only streams and will throw an exception.
     *
     * @param string $data The data to write.
     * @return Result The result containing the number of bytes written.
     */
    public function flush(): Result
    {
        throw new InvalidArgumentException(
            "Flush operation not supported on read-only stream"
        );
    }

    /**
     * Closes the stream and removes it from the event loop.
     * This method should be called when the stream is no longer needed.
     */
    public function close(): void
    {
        if (!$this->isClosed && $this->socket) {
            $this->isClosed = true;
            VOsaka::getLoop()->removeReadStream($this->socket);
        }
    }
}
