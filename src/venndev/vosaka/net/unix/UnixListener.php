<?php

declare(strict_types=1);

namespace venndev\vosaka\net\unix;

use Generator;
use InvalidArgumentException;
use venndev\vosaka\core\Result;
use venndev\vosaka\core\Future;
use venndev\vosaka\net\ListenerInterface;
use venndev\vosaka\net\SocketBase;
use venndev\vosaka\VOsaka;
use venndev\vosaka\platform\PlatformOptionsFactory;
use venndev\vosaka\net\SocketOptions;

final class UnixListener extends SocketBase implements ListenerInterface
{
    private bool $isListening = false;

    private function __construct(
        private readonly string $path,
        array $options = []
    ) {
        $this->options = self::normalizeOptions($options);
    }

    /**
     * Binds a Unix domain socket listener to the specified path.
     *
     * @param string $path The path to bind the socket to.
     * @param array $options Optional socket options.
     * @return Result<UnixListener> A Result containing the UnixListener on success.
     * @throws InvalidArgumentException If the path is invalid or binding fails.
     */
    public static function bind(string $path, array $options = []): Result
    {
        $fn = function () use ($path, $options): Generator {
            self::validatePath($path);
            $listener = new self($path, $options);
            yield from $listener->bindSocket()->unwrap();
            return $listener;
        };

        return Future::new($fn());
    }

    private function bindSocket(): Result
    {
        $fn = function (): Generator {
            if ($this->options["remove_existing"] && file_exists($this->path)) {
                if (!unlink($this->path)) {
                    throw new InvalidArgumentException(
                        "Failed to remove existing socket file: {$this->path}"
                    );
                }
            }

            $context = self::createContext($this->options);

            $this->socket = @stream_socket_server(
                "unix://{$this->path}",
                $errno,
                $errstr,
                STREAM_SERVER_BIND | STREAM_SERVER_LISTEN,
                $context
            );

            if (!$this->socket) {
                throw new InvalidArgumentException(
                    "Failed to bind to {$this->path}: $errstr (errno: $errno)"
                );
            }

            self::addToEventLoop($this->socket);
            if (file_exists($this->path)) {
                chmod($this->path, $this->options["permissions"]);
            }

            self::applySocketOptions($this->socket, $this->options);
            $this->isListening = true;

            yield;
        };

        return Future::new($fn());
    }

    /**
     * Accepts a new connection on the Unix domain socket.
     *
     * @param float $timeout Optional timeout in seconds for accepting a connection.
     * @return Result<UnixStream> A Result containing the UnixStream on success.
     * @throws InvalidArgumentException If the listener is not bound or accept fails.
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

            if ($clientSocket) {
                self::addToEventLoop($clientSocket);
                self::applySocketOptions($clientSocket, $this->options);
                return new UnixStream(
                    $clientSocket,
                    $peerName ?: "unix:unknown"
                );
            }

            return null;
        };

        return Future::new($fn());
    }

    /**
     * Returns the local address of the Unix domain socket.
     *
     * @return string The path to the Unix socket.
     */
    public function localAddr(): string
    {
        return $this->path;
    }

    /**
     * Returns the options used for the UnixListener.
     *
     * @return array The socket options.
     */
    public function getOptions(): array
    {
        return $this->options;
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
     * Checks if the listener is currently listening for connections.
     *
     * @return bool True if the listener is listening, false otherwise.
     */
    public function close(): void
    {
        if ($this->socket) {
            self::removeFromEventLoop($this->socket);
            $this->socket = null;
        }
        $this->isListening = false;

        if (file_exists($this->path)) {
            @unlink($this->path);
        }
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
