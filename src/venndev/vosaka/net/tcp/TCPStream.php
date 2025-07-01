<?php

declare(strict_types=1);

namespace venndev\vosaka\net\tcp;

use Generator;
use InvalidArgumentException;
use venndev\vosaka\core\Result;
use venndev\vosaka\VOsaka;

/**
 * TCPStream provides asynchronous TCP stream operations.
 *
 * This class handles bidirectional TCP communication with non-blocking I/O,
 * buffering for optimal performance, and integration with the VOsaka event loop.
 * It supports reading, writing, and proper resource cleanup.
 */
final class TCPStream
{
    private bool $isClosed = false;
    private int $bufferSize = 524288; // 512KB for maximum throughput
    private string $readBuffer = "";
    private string $writeBuffer = "";
    private bool $writeRegistered = false;

    public function __construct(
        private mixed $socket,
        private readonly string $peerAddr
    ) {
        stream_set_blocking($socket, false);
        VOsaka::getLoop()->addReadStream($this->socket, [$this, "handleRead"]);
        VOsaka::getLoop()->getGracefulShutdown()->addSocket($socket);
    }

    /**
     * Handle incoming data from the socket.
     */
    public function handleRead(): void
    {
        if ($this->isClosed) {
            return;
        }

        $readCount = 0;
        $totalRead = 0;
        $maxReadCycles = 10;
        $maxBytesPerCycle = 2097152; // 2MB

        while ($readCount < $maxReadCycles && $totalRead < $maxBytesPerCycle) {
            $data = @fread($this->socket, $this->bufferSize);
            
            if ($data === false) {
                $this->close();
                return;
            }
            
            if ($data === "") {
                if (feof($this->socket)) {
                    $this->close();
                    return;
                }
                break;
            }

            $this->readBuffer .= $data;
            $totalRead += strlen($data);
            $readCount++;
            
            if (strlen($data) < $this->bufferSize) {
                break;
            }
        }
    }

    /**
     * Handle outgoing data to the socket.
     */
    public function handleWrite(): void
    {
        if ($this->isClosed || empty($this->writeBuffer)) {
            VOsaka::getLoop()->removeWriteStream($this->socket);
            $this->writeRegistered = false;
            return;
        }

        $writeCount = 0;
        $maxWriteCycles = 5;

        while (!empty($this->writeBuffer) && $writeCount < $maxWriteCycles) {
            $bytesWritten = @fwrite($this->socket, $this->writeBuffer);
            
            if ($bytesWritten === false) {
                $this->close();
                return;
            }

            if ($bytesWritten === 0) {
                break;
            }

            $this->writeBuffer = substr($this->writeBuffer, $bytesWritten);
            $writeCount++;
        }

        if (empty($this->writeBuffer)) {
            VOsaka::getLoop()->removeWriteStream($this->socket);
            $this->writeRegistered = false;
        }
    }

    /**
     * Read data from the stream.
     *
     * @param int|null $maxBytes Maximum bytes to read (null for all available)
     * @return Result<string> The read data
     */
    public function read(int|null $maxBytes = null): Result
    {
        $fn = function () use ($maxBytes): Generator {
            if (!empty($this->readBuffer)) {
                $bytes = $maxBytes ?? strlen($this->readBuffer);
                $data = substr($this->readBuffer, 0, $bytes);
                $this->readBuffer = substr($this->readBuffer, $bytes);
                return $data;
            }

            while (empty($this->readBuffer) && !$this->isClosed) {
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

        return Result::c($fn());
    }

    /**
     * Read exact number of bytes from the stream.
     *
     * @param int $bytes Number of bytes to read
     * @return Result<string> The read data
     * @throws InvalidArgumentException If stream is closed before reading complete
     */
    public function readExact(int $bytes): Result
    {
        $fn = function () use ($bytes): Generator {
            while (strlen($this->readBuffer) < $bytes && !$this->isClosed) {
                yield;
            }

            if ($this->isClosed) {
                throw new InvalidArgumentException("Stream closed");
            }

            $data = substr($this->readBuffer, 0, $bytes);
            $this->readBuffer = substr($this->readBuffer, $bytes);
            return $data;
        };

        return Result::c($fn());
    }

    /**
     * Read until a delimiter is found.
     *
     * @param string $delimiter The delimiter to read until
     * @return Result<string> The read data (excluding delimiter)
     * @throws InvalidArgumentException If stream is closed before delimiter found
     */
    public function readUntil(string $delimiter): Result
    {
        $fn = function () use ($delimiter): Generator {
            while (($pos = strpos($this->readBuffer, $delimiter)) === false && !$this->isClosed) {
                yield;
            }

            if ($this->isClosed) {
                throw new InvalidArgumentException("Stream closed");
            }

            $data = substr($this->readBuffer, 0, $pos);
            $this->readBuffer = substr($this->readBuffer, $pos + strlen($delimiter));
            return $data;
        };

        return Result::c($fn());
    }

    /**
     * Read a line from the stream (until newline).
     *
     * @return Result<string> The read line (excluding newline)
     */
    public function readLine(): Result
    {
        return $this->readUntil("\n");
    }

    /**
     * Write data to the stream.
     *
     * @param string $data Data to write
     * @return Result<int> Number of bytes written
     * @throws InvalidArgumentException If stream is closed or write fails
     */
    public function write(string $data): Result
    {
        $fn = function () use ($data): Generator {
            if ($this->isClosed) {
                throw new InvalidArgumentException("Stream is closed");
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

            if (!$this->writeRegistered) {
                VOsaka::getLoop()->addWriteStream($this->socket, [$this, "handleWrite"]);
                $this->writeRegistered = true;
            }

            while (!empty($this->writeBuffer) && !$this->isClosed) {
                yield;
            }

            return strlen($data);
        };

        return Result::c($fn());
    }

    /**
     * Write all data to the stream (alias for write).
     *
     * @param string $data Data to write
     * @return Result<int> Number of bytes written
     */
    public function writeAll(string $data): Result
    {
        return $this->write($data);
    }

    /**
     * Flush the stream buffer.
     *
     * @return Result<void>
     */
    public function flush(): Result
    {
        $fn = function (): Generator {
            yield;
            if ($this->socket && !$this->isClosed) {
                @fflush($this->socket);
            }
        };

        return Result::c($fn());
    }

    /**
     * Get the peer address.
     *
     * @return string The peer address
     */
    public function peerAddr(): string
    {
        return $this->peerAddr;
    }

    /**
     * Check if the stream is closed.
     *
     * @return bool True if closed
     */
    public function isClosed(): bool
    {
        return $this->isClosed;
    }

    /**
     * Close the stream and cleanup resources.
     */
    public function close(): void
    {
        if (!$this->isClosed) {
            $this->isClosed = true;

            VOsaka::getLoop()->removeReadStream($this->socket);
            if ($this->writeRegistered) {
                VOsaka::getLoop()->removeWriteStream($this->socket);
            }
            VOsaka::getLoop()->getGracefulShutdown()->removeSocket($this->socket);

            @fclose($this->socket);
            $this->socket = null;
        }
    }

    /**
     * Split the stream into separate read and write halves.
     *
     * @return array{TCPReadHalf, TCPWriteHalf} Array containing read and write halves
     */
    public function split(): array
    {
        return [
            new TCPReadHalf($this->socket, $this->peerAddr),
            new TCPWriteHalf($this->socket, $this->peerAddr),
        ];
    }
}