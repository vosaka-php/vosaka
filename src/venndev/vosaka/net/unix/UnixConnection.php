<?php

declare(strict_types=1);

namespace venndev\vosaka\net\unix;

use venndev\vosaka\core\Result;
use venndev\vosaka\core\Future;
use venndev\vosaka\net\contracts\AddressInterface;
use venndev\vosaka\net\contracts\StreamInterface;
use venndev\vosaka\net\exceptions\NetworkException;
use venndev\vosaka\net\exceptions\ConnectionException;
use venndev\vosaka\net\AbstractConnection;
use venndev\vosaka\net\unix\UnixAddress;
use Generator;

/**
 * Unix Socket Connection implementation
 */
class UnixConnection extends AbstractConnection implements StreamInterface
{
    public function __construct($socket, UnixAddress $localAddress, UnixAddress $remoteAddress)
    {
        parent::__construct($socket);
        $this->localAddress = $localAddress;
        $this->remoteAddress = $remoteAddress;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocalAddress(): AddressInterface
    {
        return $this->localAddress;
    }

    /**
     * {@inheritdoc}
     */
    public function getRemoteAddress(): AddressInterface
    {
        return $this->remoteAddress;
    }

    /**
     * Read a line from the connection asynchronously.
     *
     * @return Result<string>
     */
    public function readLine(): Result
    {
        return Future::new($this->doReadLine());
    }

    /**
     * Read a line from the connection.
     * This method is a generator that yields the line read from the buffer.
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
     * Read data until a specific delimiter is found.
     *
     * @param string $delimiter Delimiter to read until
     * @return Result<string>
     */
    public function readUntil(string $delimiter): Result
    {
        return Future::new($this->doReadUntil($delimiter));
    }

    /**
     * Read data until a specific delimiter is found.
     * This method is a generator that yields the data read until the delimiter.
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
     * Read an exact number of bytes from the connection.
     * This method returns a Result that resolves to the data read.
     *
     * @param int $bytes Number of bytes to read
     * @return Result<string>
     */
    public function readExact(int $bytes): Result
    {
        return Future::new($this->doReadExact($bytes));
    }

    /**
     * Read an exact number of bytes from the connection.
     * This method is a generator that yields the data read.
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
     * Write data to the connection asynchronously.
     *
     * @param string $data Data to write
     * @return Result<int> Number of bytes written
     */
    public function writeAll(string $data): Result
    {
        return Future::new($this->doWriteAll($data));
    }

    /**
     * Write data to the connection.
     * This method is a generator that yields the number of bytes written.
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

        yield from $this->write($data)->unwrap();

        while (!$this->writeBuffer->isEmpty() && !$this->closed) {
            yield;
        }

        if ($this->closed && !$this->writeBuffer->isEmpty()) {
            throw new ConnectionException("Connection closed while writing");
        }
    }

    /**
     * Flush the write buffer asynchronously.
     *
     * @return Result
     */
    public function flush(): Result
    {
        return Future::new($this->doFlush());
    }

    /**
     * Flush the write buffer.
     * This method is a generator that yields until the buffer is empty.
     *
     * @return Generator
     * @throws NetworkException
     */
    private function doFlush(): Generator
    {
        if ($this->closed) {
            throw new NetworkException("Connection is closed");
        }

        while (!$this->writeBuffer->isEmpty() && !$this->closed) {
            yield;
        }

        if (is_resource($this->socket)) {
            @fflush($this->socket);
        }
    }

    /**
     * Check if the connection is readable.
     */
    public function readable(): bool
    {
        return !$this->closed && !$this->readBuffer->isEmpty();
    }

    /**
     * Check if the connection is writable.
     */
    public function writable(): bool
    {
        return !$this->closed;
    }
}
