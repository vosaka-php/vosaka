<?php

declare(strict_types=1);

namespace venndev\vosaka\net\unix;

use Generator;
use InvalidArgumentException;
use Throwable;
use venndev\vosaka\net\StreamBase;
use venndev\vosaka\VOsaka;

final class UnixStream extends StreamBase
{
    public function __construct(
        mixed $socket,
        private readonly string $path,
        array $options = []
    ) {
        $this->isClosed = false;
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
