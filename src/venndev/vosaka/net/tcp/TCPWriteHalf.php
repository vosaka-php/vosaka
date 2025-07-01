<?php

declare(strict_types=1);

namespace venndev\vosaka\net\tcp;

use Generator;
use InvalidArgumentException;
use venndev\vosaka\core\Result;
use venndev\vosaka\VOsaka;

/**
 * TCPWriteHalf represents the write-only half of a split TCP stream.
 *
 * This class provides write-only access to a TCP socket, allowing for
 * separation of read and write operations on the same underlying socket.
 */
final class TCPWriteHalf
{
    private bool $isClosed = false;
    private string $writeBuffer = "";
    private bool $writeRegistered = false;

    public function __construct(
        private mixed $socket,
        private readonly string $peerAddr = ""
    ) {
        if ($socket) {
            stream_set_blocking($socket, false);
        }
    }

    /**
     * Handle outgoing data to the socket.
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
     * Close the write half and cleanup resources.
     */
    public function close(): void
    {
        if (!$this->isClosed && $this->socket) {
            $this->isClosed = true;
            if ($this->writeRegistered) {
                VOsaka::getLoop()->removeWriteStream($this->socket);
                $this->writeRegistered = false;
            }
            // Note: Don't close the socket here as it might be shared with read half
        }
    }
}