<?php

declare(strict_types=1);

namespace venndev\vosaka\net;

use Generator;
use venndev\vosaka\net\contracts\AddressInterface;
use venndev\vosaka\net\contracts\ConnectionInterface;
use venndev\vosaka\net\contracts\SocketInterface;
use venndev\vosaka\net\exceptions\NetworkException;
use venndev\vosaka\core\Future;
use venndev\vosaka\core\Result;

/**
 * Base implementation for connections
 */
abstract class AbstractConnection implements ConnectionInterface, SocketInterface
{
    protected $socket;
    protected bool $closed = false;
    protected StreamBuffer $readBuffer;
    protected StreamBuffer $writeBuffer;
    protected EventLoopIntegration $eventLoop;
    protected ?AddressInterface $localAddress = null;
    protected ?AddressInterface $remoteAddress = null;
    protected float $readTimeout = 30.0;
    protected float $writeTimeout = 30.0;

    public function __construct($socket)
    {
        if (!is_resource($socket)) {
            throw new NetworkException("Invalid socket resource");
        }

        $this->socket = $socket;
        $this->readBuffer = new StreamBuffer();
        $this->writeBuffer = new StreamBuffer();
        $this->eventLoop = new EventLoopIntegration();

        // Set non-blocking
        stream_set_blocking($socket, false);

        // Setup event handlers
        $this->setupEventHandlers();
    }

    protected function setupEventHandlers(): void
    {
        // Register read handler
        $this->eventLoop->onReadable($this->socket, [$this, 'handleRead']);
    }

    /**
     * Handle readable event
     */
    public function handleRead($socket): void
    {
        if ($this->closed) {
            return;
        }

        $data = @fread($socket, 65536);

        if ($data === false || ($data === '' && feof($socket))) {
            $this->close();
            return;
        }

        if ($data !== '') {
            $this->readBuffer->append($data);
        }
    }

    /**
     * Handle writable event
     */
    public function handleWrite($socket): void
    {
        if ($this->closed || $this->writeBuffer->isEmpty()) {
            $this->eventLoop->removeWritable($socket);
            return;
        }

        $data = $this->writeBuffer->peek(8192);
        $written = @fwrite($socket, $data);

        if ($written === false) {
            $this->close();
            return;
        }

        if ($written > 0) {
            $this->writeBuffer->read($written);
        }

        if ($this->writeBuffer->isEmpty()) {
            $this->eventLoop->removeWritable($socket);
        }
    }

    /**
     * Read data from the connection
     *
     * @param int $length Number of bytes to read, -1 for all available
     * @return Result<string>
     */
    public function read(int $length = -1): Result
    {
        return Future::new($this->doRead($length));
    }

    /**
     * Read data from the connection
     *
     * @param int $length Number of bytes to read, -1 for all available
     * @return Generator<string>
     * @throws NetworkException
     */
    private function doRead(int $length): Generator
    {
        if ($this->closed) {
            throw new NetworkException("Connection is closed");
        }

        // Wait for data if buffer is empty
        while ($this->readBuffer->isEmpty() && !$this->closed) {
            yield;
        }

        if ($this->closed) {
            return '';
        }

        return $this->readBuffer->read($length);
    }

    /**
     * Write data to the connection
     *
     * @param string $data Data to write
     * @return Result<int>
     */
    public function write(string $data): Result
    {
        return Future::new($this->doWrite($data));
    }

    /**
     * Write data to the connection
     *
     * @param string $data Data to write
     * @return Generator<int>
     * @throws NetworkException
     */
    private function doWrite(string $data): Generator
    {
        if ($this->closed) {
            throw new NetworkException("Connection is closed");
        }

        if (empty($data)) {
            return 0;
        }

        // Check socket is valid
        if (!is_resource($this->socket)) {
            $this->closed = true;
            throw new NetworkException("Socket is not a valid resource");
        }

        // Try immediate write
        $written = @fwrite($this->socket, $data);

        if ($written === false) {
            // Get error details
            $error = error_get_last();
            $errorMsg = $error ? $error['message'] : 'Unknown error';

            // Check if socket is still valid
            if (!is_resource($this->socket) || feof($this->socket)) {
                $this->close();
                throw new NetworkException("Connection closed during write: " . $errorMsg);
            }

            // Retry once with yield
            yield;

            $written = @fwrite($this->socket, $data);
            if ($written === false) {
                $this->close();
                throw new NetworkException("Write failed after retry: " . $errorMsg);
            }
        }

        // If we wrote everything, return
        if ($written === strlen($data)) {
            return $written;
        }

        // Handle partial write
        if ($written > 0) {
            $remaining = substr($data, $written);
            $this->writeBuffer->append($remaining);

            // Register write handler if not already registered
            $this->eventLoop->onWritable($this->socket, [$this, 'handleWrite']);

            // Wait for buffer to be written
            while (!$this->writeBuffer->isEmpty() && !$this->closed) {
                yield;
            }

            if ($this->closed) {
                throw new NetworkException("Connection closed while writing");
            }
        }

        return strlen($data);
    }

    /**
     * Close the connection
     */
    public function close(): void
    {
        if ($this->closed) {
            return;
        }

        $this->closed = true;
        $this->eventLoop->removeAll($this->socket);

        if (is_resource($this->socket)) {
            @fclose($this->socket);
        }

        $this->readBuffer->clear();
        $this->writeBuffer->clear();
    }

    /**
     * Check if the connection is closed
     */
    public function isClosed(): bool
    {
        return $this->closed || !is_resource($this->socket);
    }

    /**
     * Set the local address for this connection
     */
    public function setReadTimeout(float $seconds): void
    {
        $this->readTimeout = $seconds;
    }

    /**
     * Set the read timeout for this connection
     */
    public function setWriteTimeout(float $seconds): void
    {
        $this->writeTimeout = $seconds;
    }

    // SocketInterface implementation

    /**
     * Get the underlying socket resource
     */
    public function getResource()
    {
        return $this->socket;
    }

    /**
     * Set a socket option
     *
     * @param int $level The level at which the option resides
     * @param int $option The option to set
     * @param mixed $value The value to set for the option
     * @return void
     */
    public function setOption(int $level, int $option, mixed $value): void
    {
        if (!function_exists('socket_import_stream')) {
            return;
        }

        $sock = @socket_import_stream($this->socket);
        if ($sock !== false) {
            @socket_set_option($sock, $level, $option, $value);
        }
    }

    /**
     * Get a socket option
     *
     * @param int $level The level at which the option resides
     * @param int $option The option to retrieve
     * @return mixed The value of the option, or null if not available
     */
    public function getOption(int $level, int $option): mixed
    {
        if (!function_exists('socket_import_stream')) {
            return null;
        }

        $sock = @socket_import_stream($this->socket);
        if ($sock !== false) {
            return @socket_get_option($sock, $level, $option);
        }

        return null;
    }

    /**
     * Set the blocking mode for the socket
     */
    public function setBlocking(bool $blocking): void
    {
        stream_set_blocking($this->socket, $blocking);
    }

    /**
     * Terminate the connection gracefully
     */
    public function shutdown(int $how = 2): void
    {
        if (is_resource($this->socket)) {
            @stream_socket_shutdown($this->socket, $how);
        }
    }
}
