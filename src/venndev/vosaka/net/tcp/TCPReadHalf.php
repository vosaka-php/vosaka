<?php

declare(strict_types=1);

namespace venndev\vosaka\net\tcp;

use Generator;
use InvalidArgumentException;
use venndev\vosaka\core\Result;
use venndev\vosaka\VOsaka;

/**
 * TCPReadHalf represents the read-only half of a split TCP stream.
 *
 * This class provides read-only access to a TCP socket, allowing for
 * separation of read and write operations on the same underlying socket.
 */
final class TCPReadHalf
{
    private bool $isClosed = false;
    private string $readBuffer = "";
    private int $bufferSize = 524288;

    public function __construct(
        private mixed $socket,
        private readonly string $peerAddr = ""
    ) {
        if ($socket) {
            stream_set_blocking($socket, false);
            VOsaka::getLoop()->addReadStream($this->socket, [$this, "handleRead"]);
        }
    }

    /**
     * Handle incoming data from the socket.
     */
    public function handleRead(): void
    {
        if ($this->isClosed || !$this->socket) {
            return;
        }

        $data = @fread($this->socket, $this->bufferSize);
        
        if ($data === false) {
            $this->close();
            return;
        }
        
        if ($data === "" && feof($this->socket)) {
            $this->close();
            return;
        }

        $this->readBuffer .= $data;
    }

    /**
     * Read data from the stream.
     *
     * @param int|null $maxBytes Maximum number of bytes to read
     * @return Result<string> Data read from the stream
     */
    public function read(?int $maxBytes = null): Result
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
     * @return Result<string> Data read from the stream
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
     * @return Result<string> Data read from the stream (excluding delimiter)
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
     * Read a line from the stream.
     *
     * @return Result<string> Line read from the stream (excluding newline)
     */
    public function readLine(): Result
    {
        return $this->readUntil("\n");
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
     * Close the read half and cleanup resources.
     */
    public function close(): void
    {
        if (!$this->isClosed && $this->socket) {
            $this->isClosed = true;
            VOsaka::getLoop()->removeReadStream($this->socket);
            // Note: Don't close the socket here as it might be shared with write half
        }
    }
}