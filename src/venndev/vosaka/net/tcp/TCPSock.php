<?php

declare(strict_types=1);

namespace venndev\vosaka\net\tcp;

use Generator;
use InvalidArgumentException;
use venndev\vosaka\core\Result;
use venndev\vosaka\core\Future;
use venndev\vosaka\net\SocketBase;

final class TCPSock extends SocketBase
{
    private bool $bound = false;
    private string $addr = "";
    private int $port = 0;

    private function __construct(private readonly string $family = "v4")
    {
        $this->options = [
            "keepalive" => true,
            "nodelay" => true,
            "reuseaddr" => true,
            "ssl" => false,
            "ssl_cert" => null,
            "ssl_key" => null,
            "verify_tls" => true,
            "backlog" => SOMAXCONN,
        ];
    }

    public static function newV4(): self
    {
        return new self("v4");
    }

    public static function newV6(): self
    {
        return new self("v6");
    }

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
                30,
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

    public function setReuseAddr(bool $reuseAddr): self
    {
        $this->options["reuseaddr"] = $reuseAddr;
        return $this;
    }

    public function setReusePort(bool $reusePort): self
    {
        $this->options["reuseport"] = $reusePort;
        return $this;
    }

    public function setKeepAlive(bool $keepAlive): self
    {
        $this->options["keepalive"] = $keepAlive;
        return $this;
    }

    public function setNoDelay(bool $noDelay): self
    {
        $this->options["nodelay"] = $noDelay;
        return $this;
    }

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

    public function getLocalAddr(): string
    {
        if (!$this->socket) {
            return "";
        }

        $name = stream_socket_get_name($this->socket, false);
        return $name ?: "";
    }
}
