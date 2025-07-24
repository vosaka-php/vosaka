<?php

declare(strict_types=1);

namespace venndev\vosaka\net\unix;

use venndev\vosaka\core\Result;
use venndev\vosaka\core\Future;
use venndev\vosaka\net\contracts\AddressInterface;
use venndev\vosaka\net\contracts\ServerInterface;
use venndev\vosaka\net\exceptions\NetworkException;
use venndev\vosaka\net\unix\UnixAddress;
use venndev\vosaka\net\unix\UnixConnection;
use Generator;

/**
 * Unix Socket Server implementation
 */
class UnixServer implements ServerInterface
{
    private $socket;
    private bool $closed = false;
    private UnixAddress $address;
    private array $options;

    public function __construct($socket, UnixAddress $address, array $options = [])
    {
        if (!is_resource($socket)) {
            throw new NetworkException("Invalid socket resource");
        }

        $this->socket = $socket;
        $this->address = $address;
        $this->options = $options;

        // Set non-blocking
        stream_set_blocking($socket, false);
    }

    /**
     * Accept a new client connection asynchronously.
     * This method returns a Future that resolves to a UnixConnection
     * or null if no connection is available within the timeout.
     *
     * @param float $timeout Timeout in seconds, 0.0 means no timeout.
     * @return Result<UnixConnection|null>
     */
    public function accept(float $timeout = 0.0): Result
    {
        return Future::new($this->doAccept($timeout));
    }

    /**
     * Accept a new client connection.
     * This method is a generator that yields a UnixConnection
     * or null if no connection is available within the timeout.
     *
     * @param float $timeout Timeout in seconds, 0.0 means no timeout.
     * @return Generator<UnixConnection|null>
     * @throws NetworkException
     */
    private function doAccept(float $timeout): Generator
    {
        if ($this->closed) {
            throw new NetworkException("Server is closed");
        }

        $start = microtime(true);

        while (!$this->closed) {
            $client = @stream_socket_accept($this->socket, 0);

            if ($client !== false) {
                stream_set_blocking($client, false);

                // Unix sockets don't have real addresses, use path
                $localAddress = $this->address;
                $remoteAddress = new UnixAddress("client:" . uniqid(), true);

                return new UnixConnection($client, $localAddress, $remoteAddress);
            }

            if ($timeout > 0 && (microtime(true) - $start) >= $timeout) {
                return null;
            }

            yield;
        }

        throw new NetworkException("Server closed while accepting");
    }

    /**
     * Close the server socket and clean up resources.
     */
    public function close(): void
    {
        if ($this->closed) {
            return;
        }

        $this->closed = true;

        if (is_resource($this->socket)) {
            @fclose($this->socket);
        }

        // Remove socket file if not abstract
        if (!$this->address->isAbstract() && file_exists($this->address->getPath())) {
            @unlink($this->address->getPath());
        }
    }

    /**
     * Check if the server is closed.
     */
    public function isClosed(): bool
    {
        return $this->closed || !is_resource($this->socket);
    }

    /**
     * Get the address the server is bound to.
     */
    public function getAddress(): AddressInterface
    {
        return $this->address;
    }

    /**
     * Get the options set for this server.
     */
    public function getOptions(): array
    {
        return $this->options;
    }
}
