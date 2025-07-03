<?php

declare(strict_types=1);

namespace venndev\vosaka\net\tcp;

use InvalidArgumentException;
use venndev\vosaka\core\Result;
use venndev\vosaka\net\NetworkConstants;
use venndev\vosaka\net\StreamBase;
use venndev\vosaka\VOsaka;

final class TCPReadHalf extends StreamBase
{
    public function __construct(
        mixed $socket,
        private readonly string $peerAddr = ""
    ) {
        $this->socket = $socket;
        $this->bufferSize = NetworkConstants::TCP_READ_BUFFER_SIZE;
        if ($socket) {
            self::addToEventLoop($socket);
            VOsaka::getLoop()->addReadStream($socket, [$this, "handleRead"]);
        }
    }

    /**
     * Handles reading data from the TCP socket.
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
     * Handles write operations for the TCP read half.
     * This is a no-op since this is a read-only stream.
     */
    public function handleWrite(): void
    {
        // No-op: Read-only stream
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
     * Reads data from the stream.
     * If the stream is closed, an exception is thrown.
     *
     * @param int|null $maxBytes Maximum number of bytes to read. Defaults to buffer size.
     * @return Result<string> The result containing the read data.
     */
    public function write(string $data): Result
    {
        throw new InvalidArgumentException(
            "Write operation not supported on read-only stream"
        );
    }

    /**
     * Writes all data to the stream.
     * This method is not supported for read-only streams and will throw an exception.
     *
     * @param string $data The data to write.
     * @return Result<int> The result containing the number of bytes written.
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
     * @return Result<int> The result containing the number of bytes written.
     */
    public function flush(): Result
    {
        throw new InvalidArgumentException(
            "Flush operation not supported on read-only stream"
        );
    }

    /**
     * Closes the stream and removes it from the event loop.
     * After closing, no further read operations can be performed.
     */
    public function close(): void
    {
        if (!$this->isClosed && $this->socket) {
            $this->isClosed = true;
            VOsaka::getLoop()->removeReadStream($this->socket);
        }
    }
}
