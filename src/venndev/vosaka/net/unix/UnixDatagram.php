<?php

declare(strict_types=1);

namespace venndev\vosaka\net\unix;

use Generator;
use InvalidArgumentException;
use venndev\vosaka\time\Sleep;
use venndev\vosaka\core\Result;
use venndev\vosaka\VOsaka;

/**
 * Unix datagram socket for connectionless communication.
 *
 * This class provides asynchronous Unix datagram socket functionality for
 * connectionless communication over Unix domain sockets. It supports both
 * bound and unbound sockets, allowing for flexible client-server or
 * peer-to-peer communication patterns.
 *
 * All operations are non-blocking and return Result objects that can be
 * awaited using VOsaka's async runtime. The class handles socket creation,
 * binding, and proper cleanup of socket files.
 */
final class UnixDatagram
{
    private mixed $socket = null;
    private bool $bound = false;
    private string $path = "";
    private array $options = [];

    private function __construct(array $options = [])
    {
        $this->options = array_merge(
            [
                "reuseaddr" => true,
            ],
            $options
        );
    }

    /**
     * Create a new Unix datagram socket.
     *
     * Creates a new Unix datagram socket instance that can be used for
     * connectionless communication. The socket can be bound to a path
     * or used unbound for client operations.
     *
     * Available options:
     * - 'reuseaddr' (bool): Whether to reuse the address (default: true)
     *
     * @param array $options Socket configuration options
     * @return UnixDatagram A new UnixDatagram instance
     */
    public static function new(array $options = []): self
    {
        return new self($options);
    }

    /**
     * Bind the socket to a Unix domain socket path.
     *
     * Binds the datagram socket to the specified path, creating the socket
     * file on the filesystem. The socket must be bound before it can receive
     * data. If the path already exists, it will be removed first.
     *
     * @param string $path Path to the Unix socket file
     * @return Result<self> The bound socket instance
     * @throws InvalidArgumentException If the path is invalid or binding fails
     */
    public function bind(string $path): Result
    {
        $fn = function () use ($path): Generator {
            self::validatePath($path);
            $this->path = $path;

            // Remove existing socket file if it exists
            if (file_exists($path)) {
                unlink($path);
            }

            $context = $this->createContext();

            $this->socket = @stream_socket_server(
                "udg://{$this->path}",
                $errno,
                $errstr,
                STREAM_SERVER_BIND,
                $context
            );

            if (!$this->socket) {
                throw new InvalidArgumentException(
                    "Failed to bind Unix datagram socket to {$path}: $errstr"
                );
            }

            stream_set_blocking($this->socket, false);
            VOsaka::getLoop()->getGracefulShutdown()->addSocket($this->socket);

            $this->bound = true;
            $this->configureSocket();

            yield Sleep::c(0.001); // Allow for async operation

            return $this;
        };

        return Result::c($fn());
    }

    /**
     * Send data to a specific Unix socket path.
     *
     * Sends data to the specified Unix domain socket path. The socket does
     * not need to be bound to send data, but the target path must exist
     * and have a socket listening on it.
     *
     * @param string $data Data to send
     * @param string $path Path to the target Unix socket
     * @return Result<int> Number of bytes sent
     * @throws InvalidArgumentException If sending fails
     */
    public function sendTo(string $data, string $path): Result
    {
        $fn = function () use ($data, $path): Generator {
            self::validatePath($path);

            if (!$this->socket) {
                // Create an unbound socket for sending
                $context = $this->createContext();
                $this->socket = @stream_socket_client(
                    "udg://",
                    $errno,
                    $errstr,
                    30,
                    STREAM_CLIENT_CONNECT,
                    $context
                );

                if (!$this->socket) {
                    throw new InvalidArgumentException(
                        "Failed to create Unix datagram socket: $errstr"
                    );
                }

                stream_set_blocking($this->socket, false);
                VOsaka::getLoop()
                    ->getGracefulShutdown()
                    ->addSocket($this->socket);
            }

            $result = @stream_socket_sendto($this->socket, $data, 0, $path);

            if ($result === false || $result === -1) {
                throw new InvalidArgumentException(
                    "Failed to send data to Unix socket {$path}"
                );
            }

            yield Sleep::c(0.001); // Allow for async operation

            return $result;
        };

        return Result::c($fn());
    }

    /**
     * Receive data from the Unix socket.
     *
     * Receives data from the bound Unix datagram socket. The socket must be
     * bound before it can receive data. Returns both the data and the path
     * of the sender.
     *
     * @param int $maxLength Maximum number of bytes to receive
     * @return Result<array{data: string, peerPath: string}> Received data and sender path
     * @throws InvalidArgumentException If the socket is not bound or receive fails
     */
    public function receiveFrom(int $maxLength = 65535): Result
    {
        $fn = function () use ($maxLength): Generator {
            if (!$this->bound) {
                throw new InvalidArgumentException(
                    "Socket must be bound before receiving"
                );
            }

            while (true) {
                $data = @stream_socket_recvfrom(
                    $this->socket,
                    $maxLength,
                    0,
                    $peerPath
                );

                if ($data === false) {
                    throw new InvalidArgumentException(
                        "Failed to receive data from Unix socket"
                    );
                }

                if ($data !== "") {
                    return ["data" => $data, "peerPath" => $peerPath ?? ""];
                }

                yield Sleep::c(0.001); // Non-blocking wait
            }
        };

        return Result::c($fn());
    }

    /**
     * Set socket reuse address option.
     *
     * @param bool $reuseAddr Whether to reuse the address
     * @return self This instance for method chaining
     */
    public function setReuseAddr(bool $reuseAddr): self
    {
        $this->options["reuseaddr"] = $reuseAddr;
        if ($this->socket) {
            socket_set_option(
                $this->socket,
                SOL_SOCKET,
                SO_REUSEADDR,
                $reuseAddr ? 1 : 0
            );
        }
        return $this;
    }

    /**
     * Get the local socket path.
     *
     * @return string The local socket path, empty if not bound
     */
    public function localPath(): string
    {
        if (!$this->socket) {
            return "";
        }

        $name = stream_socket_get_name($this->socket, false);
        return $name ?: "";
    }

    /**
     * Close the datagram socket.
     *
     * Closes the socket and cleans up the socket file if it was bound.
     * This method is idempotent and can be called multiple times safely.
     */
    public function close(): void
    {
        if ($this->socket) {
            VOsaka::getLoop()
                ->getGracefulShutdown()
                ->removeSocket($this->socket);
            @fclose($this->socket);
            $this->socket = null;
        }

        // Clean up socket file if we created it
        if ($this->bound && file_exists($this->path)) {
            @unlink($this->path);
        }

        $this->bound = false;
    }

    /**
     * Check if the socket is closed.
     *
     * @return bool True if the socket is closed, false otherwise
     */
    public function isClosed(): bool
    {
        return !$this->socket;
    }

    /**
     * Validate Unix domain socket path.
     *
     * @param string $path Path to validate
     * @throws InvalidArgumentException If path is invalid
     */
    private static function validatePath(string $path): void
    {
        if (empty($path)) {
            throw new InvalidArgumentException(
                "Unix socket path cannot be empty"
            );
        }

        if (strlen($path) > 108) {
            throw new InvalidArgumentException(
                "Unix socket path too long (max 108 characters)"
            );
        }

        $dir = dirname($path);
        if (!is_dir($dir)) {
            throw new InvalidArgumentException(
                "Directory does not exist: {$dir}"
            );
        }

        if (!is_writable($dir)) {
            throw new InvalidArgumentException(
                "Directory is not writable: {$dir}"
            );
        }
    }

    /**
     * Create stream context with options.
     *
     * @return resource Stream context
     */
    private function createContext()
    {
        $context = stream_context_create();
        if ($this->options["reuseaddr"]) {
            stream_context_set_option($context, "socket", "so_reuseaddr", 1);
        }
        return $context;
    }

    /**
     * Configure socket with options.
     */
    private function configureSocket(): void
    {
        if (!$this->socket) {
            return;
        }

        stream_set_blocking($this->socket, false);

        if ($this->options["reuseaddr"]) {
            socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1);
        }
    }
}
