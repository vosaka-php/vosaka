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

    /**
     * Handles reading data from the Unix socket.
     * This is a no-op since this is a write-only stream.
     */
    public function handleRead(): void
    {
        // No-op: Write-only stream
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
     *
     * @return string The peer address.
     */
    public function peerAddr(): string
    {
        return $this->stream->peerAddr();
    }

    /**
     * Writes data to the stream.
     * If the stream is closed, an exception is thrown.
     *
     * @param string $data The data to write.
     * @return Result<int> The result containing the number of bytes written.
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

    /**
     * Writes all data to the stream.
     * This method is asynchronous and returns a Result object.
     *
     * @param string $data The data to write.
     * @return Result<int> The result containing the number of bytes written.
     */
    public function read(?int $maxBytes = null): Result
    {
        throw new InvalidArgumentException(
            "Read operation not supported on write-only stream"
        );
    }

    /**
     * Writes all data to the stream.
     * This method is asynchronous and returns a Result object.
     *
     * @param string $data The data to write.
     * @return Result<int> The result containing the number of bytes written.
     */
    public function readExact(int $bytes): Result
    {
        throw new InvalidArgumentException(
            "Read operation not supported on write-only stream"
        );
    }

    /**
     * Reads data from the stream until a specific delimiter is encountered.
     * This method is not supported for write-only streams and will throw an exception.
     *
     * @param string $delimiter The delimiter to read until.
     * @return Result<string> The result containing the read data.
     */
    public function readUntil(string $delimiter): Result
    {
        throw new InvalidArgumentException(
            "Read operation not supported on write-only stream"
        );
    }

    /**
     * Reads a single line from the stream.
     * This method is not supported for write-only streams and will throw an exception.
     *
     * @return Result<string> The result containing the read line or null if closed.
     */
    public function readLine(): Result
    {
        throw new InvalidArgumentException(
            "Read operation not supported on write-only stream"
        );
    }

    /**
     * Closes the stream.
     * This method will unregister the write stream from the event loop.
     */
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
