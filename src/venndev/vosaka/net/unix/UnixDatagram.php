<?php

declare(strict_types=1);

namespace venndev\vosaka\net\unix;

use Generator;
use InvalidArgumentException;
use venndev\vosaka\core\Result;
use venndev\vosaka\core\Future;
use venndev\vosaka\net\NetworkConstants;
use venndev\vosaka\net\option\PlatformOptionsFactory;
use venndev\vosaka\net\option\SocketOptions;
use venndev\vosaka\net\SocketBase;

final class UnixDatagram extends SocketBase
{
    private bool $bound = false;
    private string $path = "";

    /**
     * Creates a new Unix datagram socket instance.
     *
     * @param array|SocketOptions $options Optional socket options.
     */
    public static function bind(
        string $path,
        array|SocketOptions $options = []
    ): Result {
        $fn = function () use ($path, $options): Generator {
            yield;
            self::validatePath($path);
            $opts = self::normalizeOptions($options);

            if (file_exists($path)) {
                unlink($path);
            }

            $context = self::createContext($opts);

            $socket = @stream_socket_server(
                "udg://{$path}",
                $errno,
                $errstr,
                STREAM_SERVER_BIND,
                $context
            );

            if (!$socket) {
                throw new InvalidArgumentException(
                    "Failed to bind Unix datagram socket to {$path}: $errstr"
                );
            }

            self::addToEventLoop($socket);
            self::applySocketOptions($socket, $opts);

            $inst = new self();
            $inst->socket = $socket;
            $inst->path = $path;
            $inst->options = $opts;
            $inst->bound = true;

            return $inst;
        };

        return Future::new($fn());
    }

    /**
     * Sends data to a Unix datagram socket.
     *
     * @param string $data The data to send.
     * @param string $path The path to the Unix socket.
     * @param array|SocketOptions $options Optional socket options.
     * @return Result<int> A Result containing the number of bytes sent on success.
     * @throws InvalidArgumentException If the path is invalid or sending fails.
     */
    public function sendTo(
        string $data,
        string $path,
        array|SocketOptions $options = []
    ): Result {
        $fn = function () use ($data, $path, $options): Generator {
            yield;
            self::validatePath($path);
            $opts = self::normalizeOptions($options);

            if (!$this->socket) {
                $context = self::createContext($opts);
                $this->socket = @stream_socket_client(
                    "udg://",
                    $errno,
                    $errstr,
                    NetworkConstants::DEFAULT_TIMEOUT,
                    STREAM_CLIENT_CONNECT,
                    $context
                );

                if (!$this->socket) {
                    throw new InvalidArgumentException(
                        "Failed to create Unix datagram socket: $errstr"
                    );
                }

                self::addToEventLoop($this->socket);
                self::applySocketOptions($this->socket, $opts);
            }

            $result = @stream_socket_sendto($this->socket, $data, 0, $path);

            if ($result === false || $result === -1) {
                throw new InvalidArgumentException(
                    "Failed to send data to Unix socket {$path}"
                );
            }

            return $result;
        };

        return Future::new($fn());
    }

    /**
     * Receives data from a Unix datagram socket.
     *
     * @param int $maxLength The maximum length of data to receive.
     * @return Result<array{data: string, peerPath: string}> A Result containing the received data and peer path.
     * @throws InvalidArgumentException If the socket is not bound or receiving fails.
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

                yield;
            }
        };

        return Future::new($fn());
    }

    /**
     * Sets the reuse address option for the socket.
     *
     * @param bool $reuseAddr Whether to allow reusing the address.
     * @return self The current instance for method chaining.
     */
    public function setReuseAddr(bool $reuseAddr): self
    {
        $this->options["reuseaddr"] = $reuseAddr;
        if ($this->socket) {
            self::applySocketOptions($this->socket, [
                "reuseaddr" => $reuseAddr,
            ]);
        }
        return $this;
    }

    /**
     * Validates the Unix socket path.
     *
     * @param string $path The path to validate.
     * @throws InvalidArgumentException If the path is invalid.
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
     * Closes the Unix datagram socket and cleans up resources.
     *
     * @throws InvalidArgumentException If the socket is already closed.
     */
    public function close(): void
    {
        if ($this->socket) {
            self::removeFromEventLoop($this->socket);
            $this->socket = null;
        }

        if ($this->bound && file_exists($this->path)) {
            @unlink($this->path);
        }

        $this->bound = false;
    }

    /**
     * Checks if the socket is closed.
     *
     * @return bool True if the socket is closed, false otherwise.
     */
    public function isClosed(): bool
    {
        return !$this->socket;
    }
}
