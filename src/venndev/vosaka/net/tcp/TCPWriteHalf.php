<?php

declare(strict_types=1);

namespace venndev\vosaka\net\tcp;

use InvalidArgumentException;
use venndev\vosaka\core\Result;
use venndev\vosaka\net\NetworkConstants;
use venndev\vosaka\net\StreamBase;
use venndev\vosaka\VOsaka;

final class TCPWriteHalf extends StreamBase
{
    public function __construct(
        mixed $socket,
        private readonly string $peerAddr = ""
    ) {
        $this->socket = $socket;
        $this->bufferSize = NetworkConstants::TCP_WRITE_BUFFER_SIZE;
        if ($socket) {
            self::addToEventLoop($socket);
        }
    }

    /**
     * Handles reading data from the TCP socket.
     * This is a no-op since this is a write-only stream.
     */
    public function handleRead(): void
    {
        // No-op: Write-only stream
    }

    /**
     * Handles write operations for the TCP stream.
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
     * Returns the peer address of the TCP connection.
     * This is typically the address of the remote host.
     *
     * @return string The peer address.
     */
    public function peerAddr(): string
    {
        return $this->peerAddr;
    }

    /**
     * Writes data to the stream.
     * This method is asynchronous and returns a Result object.
     *
     * @param string $data The data to write.
     * @return Result The result of the write operation.
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
     * @return Result The result of the write operation.
     */
    public function readExact(int $bytes): Result
    {
        throw new InvalidArgumentException(
            "Read operation not supported on write-only stream"
        );
    }

    /**
     * Reads data until a specific delimiter is encountered.
     * This method is asynchronous and returns a Result object.
     *
     * @param string $delimiter The delimiter to read until.
     * @return Result The result of the read operation.
     */
    public function readUntil(string $delimiter): Result
    {
        throw new InvalidArgumentException(
            "Read operation not supported on write-only stream"
        );
    }

    /**
     * Reads a single line from the stream.
     * This method is asynchronous and returns a Result object.
     *
     * @return Result The result of the read operation.
     */
    public function readLine(): Result
    {
        throw new InvalidArgumentException(
            "Read operation not supported on write-only stream"
        );
    }

    /**
     * Closes the TCP stream.
     * This method will remove the write stream from the event loop if it is registered.
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
