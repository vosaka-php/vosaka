<?php

declare(strict_types=1);

namespace venndev\vosaka\net\tcp;

use Generator;
use InvalidArgumentException;
use venndev\vosaka\core\Result;
use venndev\vosaka\core\Future;
use venndev\vosaka\net\NetworkConstants;
use venndev\vosaka\net\SocketBase;

final class TCPSock extends SocketBase
{
    private bool $bound = false;
    private string $addr = "";
    private int $port = 0;

    private function __construct(
        private readonly string $family = "v4",
        array $options = []
    ) {
        $this->options = self::normalizeOptions($options);
    }

    /**
     * Creates a new TCP socket instance.
     *
     * @param string $family The address family ("v4" or "v6").
     * @param array $options Optional socket options.
     */
    public static function newV4(array $options = []): self
    {
        return new self("v4", $options);
    }

    /**
     * Creates a new TCP socket instance for IPv6.
     *
     * @param array $options Optional socket options.
     */
    public static function newV6(array $options = []): self
    {
        return new self("v6", $options);
    }

    /**
     * Parses the address into host and port.
     *
     * @param string $addr The address in "host:port" format.
     * @return array<string, int> An array containing the host and port.
     */
    public function bind(string $addr): Result
    {
        $fn = function () use ($addr): Generator {
            yield;
            [$this->addr, $this->port] = self::parseAddr($addr);
            $context = self::createContext($this->options);

            $this->socket = @stream_socket_server(
                "tcp://{$this->addr}:{$this->port}",
                $errno,
                $errstr,
                STREAM_SERVER_BIND,
                $context
            );

            if (!$this->socket) {
                throw new InvalidArgumentException(
                    "Bind failed: $errstr ($errno)"
                );
            }

            self::addToEventLoop($this->socket);
            self::applySocketOptions($this->socket, $this->options);
            $this->bound = true;

            return $this;
        };

        return Future::new($fn());
    }

    /**
     * Listens for incoming connections on the bound address.
     *
     * @param int $backlog The maximum number of pending connections.
     * @return Result<TCPListener> A Result containing the TCPListener on success.
     * @throws InvalidArgumentException If the socket is not bound.
     */
    public function listen(int $backlog = SOMAXCONN): Result
    {
        $fn = function () use ($backlog): Generator {
            yield;
            if (!$this->bound) {
                throw new InvalidArgumentException(
                    "Socket must be bound before listening"
                );
            }

            $protocol = $this->options["ssl"] ? "ssl" : "tcp";
            $context = self::createContext($this->options);

            if (!stream_socket_shutdown($this->socket, STREAM_SHUT_RDWR)) {
                $this->removeFromEventLoop($this->socket);

                $this->socket = @stream_socket_server(
                    "{$protocol}://{$this->addr}:{$this->port}",
                    $errno,
                    $errstr,
                    STREAM_SERVER_BIND | STREAM_SERVER_LISTEN,
                    $context
                );

                if (!$this->socket) {
                    throw new InvalidArgumentException(
                        "Listen failed: $errstr ($errno)"
                    );
                }

                self::addToEventLoop($this->socket);
                self::applySocketOptions($this->socket, $this->options);
            }

            return new TCPListener($this->addr, $this->port, $this->options);
        };

        return Future::new($fn());
    }

    /**
     * Connects to a TCP server at the specified address.
     *
     * @param string $addr The address to connect to, in the format "host:port".
     * @return Result<TCPStream> A Result containing the TCPStream on success.
     * @throws InvalidArgumentException If the address is invalid or connection fails.
     */
    public function connect(string $addr): Result
    {
        $fn = function () use ($addr): Generator {
            yield;
            [$host, $port] = self::parseAddr($addr);
            $protocol = $this->options["ssl"] ? "ssl" : "tcp";
            $context = self::createContext($this->options);

            $this->socket = @stream_socket_client(
                "{$protocol}://{$host}:{$port}",
                $errno,
                $errstr,
                NetworkConstants::DEFAULT_TIMEOUT,
                STREAM_CLIENT_CONNECT,
                $context
            );

            if (!$this->socket) {
                throw new InvalidArgumentException(
                    "Connect failed: $errstr ($errno)"
                );
            }

            self::addToEventLoop($this->socket);
            self::applySocketOptions($this->socket, $this->options);

            return new TCPStream($this->socket, $host . ":" . $port);
        };

        return Future::new($fn());
    }

    /**
     * Sets the socket option to reuse the address.
     *
     * @param bool $reuseAddr Whether to reuse the address.
     * @return self
     */
    public function setReuseAddr(bool $reuseAddr): self
    {
        $this->options["reuseaddr"] = $reuseAddr;
        return $this;
    }

    /**
     * Sets the socket option to reuse the port.
     *
     * @param bool $reusePort Whether to reuse the port.
     * @return self
     */
    public function setReusePort(bool $reusePort): self
    {
        $this->options["reuseport"] = $reusePort;
        return $this;
    }

    /**
     * Sets the socket option to keep the connection alive.
     *
     * @param bool $keepAlive Whether to keep the connection alive.
     * @return self
     */
    public function setKeepAlive(bool $keepAlive): self
    {
        $this->options["keepalive"] = $keepAlive;
        return $this;
    }

    /**
     * Sets the socket option to disable Nagle's algorithm.
     *
     * @param bool $noDelay Whether to disable Nagle's algorithm.
     * @return self
     */
    public function setNoDelay(bool $noDelay): self
    {
        $this->options["nodelay"] = $noDelay;
        return $this;
    }

    /**
     * Sets the socket option for SSL/TLS.
     *
     * @param bool $ssl Whether to enable SSL/TLS.
     * @param string|null $sslCert Path to the SSL certificate file.
     * @param string|null $sslKey Path to the SSL key file.
     * @return self
     */
    public function setSsl(
        bool $ssl,
        ?string $sslCert = null,
        ?string $sslKey = null
    ): self {
        $this->options["ssl"] = $ssl;
        $this->options["ssl_cert"] = $sslCert;
        $this->options["ssl_key"] = $sslKey;
        return $this;
    }

    /**
     * Returns the address family of the socket.
     *
     * @return string The address family ("v4" or "v6").
     */
    public function getLocalAddr(): string
    {
        if (!$this->socket) {
            return "";
        }

        $name = stream_socket_get_name($this->socket, false);
        return $name ?: "";
    }
}
