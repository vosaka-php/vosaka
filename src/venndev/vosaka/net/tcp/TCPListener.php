<?php

declare(strict_types=1);

namespace venndev\vosaka\net\tcp;

use Generator;
use InvalidArgumentException;
use venndev\vosaka\core\Result;
use venndev\vosaka\core\Future;
use venndev\vosaka\net\ListenerInterface;
use venndev\vosaka\net\SocketBase;

final class TCPListener extends SocketBase implements ListenerInterface
{
    private bool $isListening = false;

    private function __construct(
        private readonly string $host,
        private readonly int $port,
        array $options = []
    ) {
        $this->options = self::normalizeOptions($options);
    }

    /**
     * Binds a TCP listener to the specified address.
     *
     * @param string $addr The address to bind to, in the format "host:port".
     * @param array $options Optional socket options.
     * @return Result<TCPListener> A Result containing the TCPListener on success.
     * @throws InvalidArgumentException If the address is invalid or binding fails.
     */
    public static function bind(string $addr, array $options = []): Result
    {
        $fn = function () use ($addr, $options): Generator {
            [$host, $port] = self::parseAddr($addr);
            $listener = new self($host, $port, $options);
            yield from $listener->bindSocket()->unwrap();
            return $listener;
        };

        return Future::new($fn());
    }

    private function bindSocket(): Result
    {
        $fn = function (): Generator {
            $protocol = $this->options["ssl"] ?? "tcp";
            $context = self::createContext($this->options);

            $this->socket = @stream_socket_server(
                "{$protocol}://{$this->host}:{$this->port}",
                $errno,
                $errstr,
                STREAM_SERVER_BIND | STREAM_SERVER_LISTEN,
                $context
            );

            if (!$this->socket) {
                throw new InvalidArgumentException(
                    "Failed to bind to {$this->host}:{$this->port}: $errstr (errno: $errno)"
                );
            }

            self::addToEventLoop($this->socket);
            self::applySocketOptions($this->socket, $this->options);
            $this->isListening = true;

            yield;
        };

        return Future::new($fn());
    }

    /**
     * Accepts a new incoming connection.
     *
     * @param float $timeout Optional timeout in seconds for accepting connections.
     * @return Result<TCPStream|null> A Result containing the TCPStream on success, or null if no connection is available.
     * @throws InvalidArgumentException If the listener is not bound.
     */
    public function accept(float $timeout = 0.0): Result
    {
        $fn = function () use ($timeout): Generator {
            yield;
            if (!$this->isListening) {
                throw new InvalidArgumentException("Listener is not bound");
            }

            $clientSocket = @stream_socket_accept(
                $this->socket,
                $timeout,
                $peerName
            );

            if (!$clientSocket) {
                return null;
            }

            self::addToEventLoop($clientSocket);
            if ($this->options["nodelay"]) {
                self::applySocketOptions($clientSocket, ["nodelay" => true]);
            }

            return new TCPStream($clientSocket, $peerName);
        };

        return Future::new($fn());
    }

    /**
     * Returns the local address of the listener.
     *
     * @return string The local address in the format "host:port".
     */
    public function localAddr(): string
    {
        return "{$this->host}:{$this->port}";
    }

    /**
     * Returns the options used for this listener.
     *
     * @return array The socket options.
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Checks if the listener is currently listening for connections.
     *
     * @return bool True if the listener is listening, false otherwise.
     */
    public function isReusePortEnabled(): bool
    {
        return $this->options["reuseport"];
    }

    /**
     * Returns the underlying socket resource.
     *
     * @return resource|null The socket resource, or null if not bound.
     */
    public function getSocket()
    {
        return $this->socket;
    }

    /**
     * Closes the listener and releases the socket resource.
     */
    public function close(): void
    {
        if ($this->socket) {
            self::removeFromEventLoop($this->socket);
            $this->socket = null;
        }
        $this->isListening = false;
    }

    /**
     * Checks if the listener is closed.
     *
     * @return bool True if the listener is closed, false otherwise.
     */
    public function isClosed(): bool
    {
        return !$this->isListening;
    }
}
