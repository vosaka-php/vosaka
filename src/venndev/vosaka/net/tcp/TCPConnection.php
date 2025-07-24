<?php

declare(strict_types=1);

namespace venndev\vosaka\net\tcp;

use venndev\vosaka\core\Result;
use venndev\vosaka\core\Future;
use venndev\vosaka\net\contracts\AddressInterface;
use venndev\vosaka\net\contracts\StreamInterface;
use venndev\vosaka\net\exceptions\NetworkException;
use venndev\vosaka\net\exceptions\ConnectionException;
use venndev\vosaka\net\AbstractConnection;
use Generator;

/**
 * TCP Connection implementation
 */
class TCPConnection extends AbstractConnection implements StreamInterface
{
    public function __construct($socket, AddressInterface $localAddress, AddressInterface $remoteAddress)
    {
        parent::__construct($socket);
        $this->localAddress = $localAddress;
        $this->remoteAddress = $remoteAddress;
    }

    /**
     * Get the local address of the connection
     * 
     * @return AddressInterface
     */
    public function getLocalAddress(): AddressInterface
    {
        if ($this->localAddress === null && is_resource($this->socket)) {
            $name = stream_socket_get_name($this->socket, false);
            if ($name !== false) {
                $this->localAddress = TCPAddress::parse($name);
            }
        }

        return $this->localAddress;
    }

    /**
     * Get the remote address of the connection
     *
     * @return AddressInterface
     */
    public function getRemoteAddress(): AddressInterface
    {
        if ($this->remoteAddress === null && is_resource($this->socket)) {
            $name = stream_socket_get_name($this->socket, true);
            if ($name !== false) {
                $this->remoteAddress = TCPAddress::parse($name);
            }
        }
        return $this->remoteAddress;
    }

    /**
     * Read a line from the stream
     *
     * @return Result<string>
     */
    public function readLine(): Result
    {
        return Future::new($this->doReadLine());
    }

    /**
     * Internal method to read a line from the stream
     *
     * @return Generator<string>
     * @throws NetworkException
     * @throws ConnectionException
     */
    private function doReadLine(): Generator
    {
        if ($this->closed) {
            throw new NetworkException("Connection is closed");
        }

        // Check if we already have a line in buffer
        $line = $this->readBuffer->readLine();
        if ($line !== null) {
            return $line;
        }

        // Wait for a complete line
        while (!$this->closed) {
            $line = $this->readBuffer->readLine();
            if ($line !== null) {
                return $line;
            }
            yield;
        }

        throw new ConnectionException("Connection closed while reading line");
    }

    /**
     * Read data until a specific delimiter
     * 
     * @param string $delimiter Delimiter to read until
     * @return Result<string>
     */
    public function readUntil(string $delimiter): Result
    {
        return Future::new($this->doReadUntil($delimiter));
    }

    /**
     * Internal method to read data until a specific delimiter
     *
     * @param string $delimiter Delimiter to read until
     * @return Generator<string>
     * @throws NetworkException
     * @throws ConnectionException
     */
    private function doReadUntil(string $delimiter): Generator
    {
        if ($this->closed) {
            throw new NetworkException("Connection is closed");
        }

        // Check if we already have the delimiter in buffer
        $data = $this->readBuffer->readUntil($delimiter);
        if ($data !== null) {
            return $data;
        }

        // Wait for delimiter
        while (!$this->closed) {
            $data = $this->readBuffer->readUntil($delimiter);
            if ($data !== null) {
                return $data;
            }
            yield;
        }

        throw new ConnectionException("Connection closed while reading");
    }

    /**
     * Read an exact number of bytes from the stream
     *
     * @param int $bytes Number of bytes to read
     * @return Result<string>
     */
    public function readExact(int $bytes): Result
    {
        return Future::new($this->doReadExact($bytes));
    }

    /**
     * Internal method to read an exact number of bytes from the stream
     *
     * @param int $bytes Number of bytes to read
     * @return Generator<string>
     * @throws NetworkException
     * @throws ConnectionException
     */
    private function doReadExact(int $bytes): Generator
    {
        if ($this->closed) {
            throw new NetworkException("Connection is closed");
        }

        if ($bytes <= 0) {
            return '';
        }

        $data = '';
        $remaining = $bytes;

        while ($remaining > 0 && !$this->closed) {
            if ($this->readBuffer->getSize() > 0) {
                $chunk = $this->readBuffer->read($remaining);
                $data .= $chunk;
                $remaining -= strlen($chunk);
            } else {
                yield;
            }
        }

        if ($remaining > 0) {
            throw new ConnectionException("Connection closed while reading");
        }

        return $data;
    }

    /**
     * Write data to the stream
     *
     * @param string $data Data to write
     * @return Result<int>
     */
    public function writeAll(string $data): Result
    {
        return Future::new($this->doWriteAll($data));
    }

    /**
     * Internal method to write all data to the stream
     *
     * @param string $data Data to write
     * @return Generator<int>
     * @throws NetworkException
     * @throws ConnectionException
     */
    private function doWriteAll(string $data): Generator
    {
        if ($this->closed) {
            throw new NetworkException("Connection is closed");
        }

        if (empty($data)) {
            return;
        }

        $totalWritten = 0;
        $remaining = $data;

        while ($totalWritten < strlen($data) && !$this->closed) {
            try {
                $written = yield from $this->write($remaining)->unwrap();
                $totalWritten += $written;

                if ($totalWritten >= strlen($data)) {
                    break;
                }

                $remaining = substr($data, $totalWritten);

                // Small yield to prevent tight loop
                yield;
            } catch (\Exception $e) {
                throw new ConnectionException(
                    "Failed to write all data: wrote {$totalWritten} of " . strlen($data) . " bytes. " . $e->getMessage()
                );
            }
        }

        // Wait for write buffer to be empty
        while (!$this->writeBuffer->isEmpty() && !$this->closed) {
            yield;
        }

        if ($this->closed && !$this->writeBuffer->isEmpty()) {
            throw new ConnectionException("Connection closed while writing");
        }
    }

    /**
     * Flush the write buffer
     *
     * @return Result
     */
    public function flush(): Result
    {
        return Future::new($this->doFlush());
    }

    /**
     * Internal method to flush the write buffer
     *
     * @return Generator
     * @throws NetworkException
     */
    private function doFlush(): Generator
    {
        if ($this->closed) {
            throw new NetworkException("Connection is closed");
        }

        // Wait for write buffer to be empty
        while (!$this->writeBuffer->isEmpty() && !$this->closed) {
            yield;
        }

        if (is_resource($this->socket)) {
            @fflush($this->socket);
        }
    }

    /**
     * Check if the connection is readable
     *
     * @return bool
     */
    public function readable(): bool
    {
        return !$this->closed && !$this->readBuffer->isEmpty();
    }

    /**
     * Check if the connection is writable
     *
     * @return bool
     */
    public function writable(): bool
    {
        return !$this->closed && $this->writeBuffer->getSize() < 65536; // Arbitrary limit
    }

    /**
     * Enable TCP keepalive
     */
    public function setKeepAlive(bool $enable, int $idle = 7200, int $interval = 75, int $count = 9): void
    {
        if ($enable) {
            $this->setOption(SOL_SOCKET, SO_KEEPALIVE, 1);

            // Platform-specific keepalive options
            if (defined('TCP_KEEPIDLE')) {
                $this->setOption(SOL_TCP, TCP_KEEPIDLE, $idle);
            }
            if (defined('TCP_KEEPINTVL')) {
                $this->setOption(SOL_TCP, TCP_KEEPINTVL, $interval);
            }
            if (defined('TCP_KEEPCNT')) {
                $this->setOption(SOL_TCP, TCP_KEEPCNT, $count);
            }
        } else {
            $this->setOption(SOL_SOCKET, SO_KEEPALIVE, 0);
        }
    }

    /**
     * Enable/disable Nagle's algorithm
     */
    public function setNoDelay(bool $noDelay): void
    {
        $this->setOption(SOL_TCP, TCP_NODELAY, $noDelay ? 1 : 0);
    }
}
