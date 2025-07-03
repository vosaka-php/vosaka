<?php

declare(strict_types=1);

namespace venndev\vosaka\net\udp;

use Generator;
use InvalidArgumentException;
use venndev\vosaka\core\Result;
use venndev\vosaka\core\Future;
use venndev\vosaka\net\DatagramInterface;
use venndev\vosaka\net\SocketBase;
use venndev\vosaka\net\option\SocketOptions;

/**
 * UDPSock provides asynchronous UDP socket operations.
 *
 * This class handles UDP socket creation, binding, sending, and receiving
 * with support for both IPv4 and IPv6 protocols. It integrates with the
 * VOsaka event loop for non-blocking operations.
 */
final class UDPSock extends SocketBase implements DatagramInterface
{
    private bool $bound = false;
    private string $addr = "";
    private int $port = 0;

    private function __construct(
        private readonly string $family = "v4",
        array|SocketOptions $options = []
    ) {
        $this->options = self::normalizeOptions($options);
    }

    /**
     * Create a new IPv4 UDP socket.
     *
     * @param array|SocketOptions $options Socket options
     * @return self New UDPSock instance for IPv4
     */
    public static function newV4(array|SocketOptions $options = []): self
    {
        return new self("v4", $options);
    }

    /**
     * Create a new IPv6 UDP socket.
     *
     * @param array|SocketOptions $options Socket options
     * @return self New UDPSock instance for IPv6
     */
    public static function newV6(array|SocketOptions $options = []): self
    {
        return new self("v6", $options);
    }

    /**
     * Bind the socket to the specified address and port.
     *
     * @param string $addr Address in 'host:port' format
     * @param array|SocketOptions $options Socket options
     * @return Result<UDPSock> Result containing this UDPSock instance
     * @throws InvalidArgumentException If binding fails
     */
    public function bind(
        string $addr,
        array|SocketOptions $options = []
    ): Result {
        $fn = function () use ($addr, $options): Generator {
            $opts = self::normalizeOptions($options ?: $this->options);
            [$this->addr, $this->port] = self::parseAddr($addr);
            $protocol = $this->family === "v6" ? "udp6" : "udp";
            $context = self::createContext($opts);

            $this->socket = @stream_socket_server(
                "{$protocol}://{$this->addr}:{$this->port}",
                $errno,
                $errstr,
                STREAM_SERVER_BIND,
                $context
            );

            if (! $this->socket) {
                throw new InvalidArgumentException(
                    "Bind failed: $errstr ($errno)"
                );
            }

            self::addToEventLoop($this->socket);
            self::applySocketOptions($this->socket, $opts);
            $this->bound = true;

            yield;
            return $this;
        };

        return Future::new($fn());
    }

    /**
     * Send data to a specific address.
     *
     * @param string $data Data to send
     * @param string $addr Address in 'host:port' format
     * @return Result<int> Number of bytes sent
     * @throws InvalidArgumentException If socket is not created or send fails
     */
    public function sendTo(string $data, string $addr): Result
    {
        $fn = function () use ($data, $addr): Generator {
            if (! $this->socket) {
                throw new InvalidArgumentException(
                    "Socket must be created before sending"
                );
            }

            [$host, $port] = self::parseAddr($addr);
            $result = @stream_socket_sendto(
                $this->socket,
                $data,
                0,
                "{$host}:{$port}"
            );

            if ($result === false || $result === -1) {
                $error = error_get_last();
                throw new InvalidArgumentException(
                    "Send failed: ".($error["message"] ?? "Unknown error")
                );
            }

            yield;
            return $result;
        };

        return Future::new($fn());
    }

    /**
     * Receive data from any address.
     *
     * @param int $maxLength Maximum length of data to receive
     * @return Result<array{data: string, peerAddr: string}> Received data and peer address
     * @throws InvalidArgumentException If socket is not bound or receive fails
     */
    public function receiveFrom(int $maxLength = 65535): Result
    {
        $fn = function () use ($maxLength): Generator {
            yield;
            if (! $this->bound) {
                throw new InvalidArgumentException(
                    "Socket must be bound before receiving"
                );
            }

            $data = @stream_socket_recvfrom(
                $this->socket,
                $maxLength,
                0,
                $peerAddr
            );

            if ($data === false) {
                $error = error_get_last();
                throw new InvalidArgumentException(
                    "Receive failed: ".($error["message"] ?? "Unknown error")
                );
            }

            return ["data" => $data, "peerAddr" => $peerAddr ?? ""];
        };

        return Future::new($fn());
    }

    /**
     * Set SO_REUSEADDR socket option.
     *
     * @param bool $reuseAddr Whether to enable address reuse
     * @return self This instance for method chaining
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
     * Set SO_REUSEPORT socket option.
     *
     * @param bool $reusePort Whether to enable port reuse
     * @return self This instance for method chaining
     */
    public function setReusePort(bool $reusePort): self
    {
        $this->options["reuseport"] = $reusePort;
        if ($this->socket) {
            self::applySocketOptions($this->socket, [
                "reuseport" => $reusePort,
            ]);
        }
        return $this;
    }

    /**
     * Set SO_BROADCAST socket option.
     *
     * @param bool $broadcast Whether to enable broadcast
     * @return self This instance for method chaining
     */
    public function setBroadcast(bool $broadcast): self
    {
        $this->options["broadcast"] = $broadcast;
        if ($this->socket) {
            $sock = socket_import_stream($this->socket);
            if ($sock !== false) {
                socket_set_option(
                    $sock,
                    SOL_SOCKET,
                    SO_BROADCAST,
                    $broadcast ? 1 : 0
                );
            }
        }
        return $this;
    }

    /**
     * Get the local address of the bound socket.
     *
     * @return string Local address or empty string if not bound
     */
    public function getLocalAddr(): string
    {
        if (! $this->socket) {
            return "";
        }

        $name = stream_socket_get_name($this->socket, false);
        return $name ?: "";
    }

    /**
     * Check if the socket is closed.
     *
     * @return bool True if socket is closed
     */
    public function isClosed(): bool
    {
        return ! $this->socket || ! is_resource($this->socket);
    }

    /**
     * Close the socket and cleanup resources.
     */
    public function close(): void
    {
        if ($this->socket) {
            self::removeFromEventLoop($this->socket);
            $this->socket = null;
        }
        $this->bound = false;
    }
}
