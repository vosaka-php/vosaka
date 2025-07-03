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
        $this->options = array_merge(
            [
                "reuseaddr" => true,
                "reuseport" => true,
                "backlog" => min(65535, SOMAXCONN * 8),
                "ssl" => false,
                "ssl_cert" => null,
                "ssl_key" => null,
                "nodelay" => true,
                "keepalive" => true,
                "defer_accept" => true,
                "fast_open" => true,
                "linger" => false,
                "sndbuf" => 1_048_576,
                "rcvbuf" => 1_048_576,
                "max_connections" => 50000,
            ],
            $options
        );
    }

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
            $protocol = $this->options["ssl"] ? "ssl" : "tcp";
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

    public function localAddr(): string
    {
        return "{$this->host}:{$this->port}";
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function isReusePortEnabled(): bool
    {
        return $this->options["reuseport"];
    }

    public function getSocket()
    {
        return $this->socket;
    }

    public function close(): void
    {
        if ($this->socket) {
            self::removeFromEventLoop($this->socket);
            $this->socket = null;
        }
        $this->isListening = false;
    }

    public function isClosed(): bool
    {
        return !$this->isListening;
    }
}
