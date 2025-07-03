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

final class UnixListener extends SocketBase implements ListenerInterface
{
    private bool $isListening = false;

    private function __construct(
        private readonly string $path,
        array $options = []
    ) {
        $this->options = array_merge(
            [
                "reuseaddr" => true,
                "backlog" => min(65535, SOMAXCONN * 4),
                "permissions" => 0660,
                "remove_existing" => true,
                "defer_accept" => true,
                "sndbuf" => 65536,
                "rcvbuf" => 65536,
                "max_connections" => 10000,
                "linger" => false,
            ],
            $options
        );
    }

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

    public function localAddr(): string
    {
        return $this->path;
    }

    public function getOptions(): array
    {
        return $this->options;
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

        if (file_exists($this->path)) {
            @unlink($this->path);
        }
    }

    public function isClosed(): bool
    {
        return !$this->isListening;
    }
}
