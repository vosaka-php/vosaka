<?php

declare(strict_types=1);

namespace venndev\vosaka\net\tcp;

use Generator;
use venndev\vosaka\net\exceptions\NetworkException;
use venndev\vosaka\net\contracts\ServerInterface;
use venndev\vosaka\net\contracts\AddressInterface;
use venndev\vosaka\net\SocketFactory;
use venndev\vosaka\core\Result;
use venndev\vosaka\core\Future;

/**
 * TCP Server implementation
 */
class TCPServer implements ServerInterface
{
    private $socket;
    private bool $closed = false;
    private TCPAddress $address;
    private array $options;

    public function __construct($socket, TCPAddress $address, array $options = [])
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
     * Accept a new connection
     * 
     * @param float $timeout Timeout in seconds, 0 for no timeout
     * @return Result<TCPConnection|null>
     */
    public function accept(float $timeout = 0.0): Result
    {
        return Future::new($this->doAccept($timeout));
    }

    /**
     * Internal method to handle accepting connections
     *
     * @param float $timeout Timeout in seconds, 0 for no timeout
     * @return Generator<TCPConnection|null>
     * @throws NetworkException
     */
    private function doAccept(float $timeout): Generator
    {
        if ($this->closed) {
            throw new NetworkException("Server is closed");
        }

        $start = microtime(true);

        while (!$this->closed) {
            $client = @stream_socket_accept($this->socket, 0, $peerName);

            if ($client !== false) {
                // Apply client options
                stream_set_blocking($client, false);
                SocketFactory::applyOptions($client, $this->options);

                // Parse addresses
                $localName = stream_socket_get_name($client, false);
                $localAddress = $localName ? TCPAddress::parse($localName) : $this->address;
                $remoteAddress = $peerName ? TCPAddress::parse($peerName) : null;

                return new TCPConnection($client, $localAddress, $remoteAddress);
            }

            // Check timeout
            if ($timeout > 0 && (microtime(true) - $start) >= $timeout) {
                return null;
            }

            yield;
        }

        throw new NetworkException("Server closed while accepting");
    }

    /**
     * Close the server socket
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
    }

    /**
     * Check if the server is closed
     */
    public function isClosed(): bool
    {
        return $this->closed || !is_resource($this->socket);
    }

    /**
     * Get the address this server is bound to
     */
    public function getAddress(): AddressInterface
    {
        return $this->address;
    }

    /**
     * Get the options used for this server
     */
    public function getOptions(): array
    {
        return $this->options;
    }
}
