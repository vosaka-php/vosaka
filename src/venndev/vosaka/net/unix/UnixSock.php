<?php

declare(strict_types=1);

namespace venndev\vosaka\net\unix;

use Generator;
use InvalidArgumentException;
use venndev\vosaka\core\Result;
use venndev\vosaka\VOsaka;

final class UnixSock
{
    private mixed $socket = null;
    private bool $bound = false;
    private string $path = '';
    private array $options = [];

    private function __construct()
    {
        $this->options = [
            'reuseaddr' => true,
        ];
    }

    public static function new(): self
    {
        return new self();
    }

    public function bind(string $path): Result
    {
        $fn = function () use ($path): Generator {
            $this->validatePath($path);
            $this->path = $path;

            $bindTask = function (): Generator {
                $context = $this->createContext();

                $this->socket = yield @stream_socket_server(
                    "unix://{$this->path}",
                    $errno,
                    $errstr,
                    STREAM_SERVER_BIND,
                    $context
                );
                VOsaka::getLoop()->getGracefulShutdown()->addSocket($this->socket);

                if (!$this->socket) {
                    throw new InvalidArgumentException("Bind failed: $errstr ($errno)");
                }

                $this->bound = true;
                $this->configureSocket();
            };

            yield from VOsaka::spawn($bindTask())->unwrap();

            return $this;
        };

        return VOsaka::spawn($fn());
    }

    public function listen(int $backlog = SOMAXCONN): Result
    {
        $fn = function () use ($backlog): Generator {
            if (!$this->bound) {
                throw new InvalidArgumentException("Socket must be bound before listening");
            }

            $listenTask = function () use ($backlog): Generator {
                if (!stream_socket_shutdown($this->socket, STREAM_SHUT_RDWR)) {
                    fclose($this->socket);
                    VOsaka::getLoop()->getGracefulShutdown()->cleanup();

                    $context = $this->createContext();
                    $this->socket = yield @stream_socket_server(
                        "unix://{$this->path}",
                        $errno,
                        $errstr,
                        STREAM_SERVER_BIND | STREAM_SERVER_LISTEN,
                        $context
                    );
                    VOsaka::getLoop()->getGracefulShutdown()->addSocket($this->socket);

                    if (!$this->socket) {
                        throw new InvalidArgumentException("Listen failed: $errstr ($errno)");
                    }
                }
            };

            yield from VOsaka::spawn($listenTask())->unwrap();
            return new UnixListener($this->socket, $this->path);
        };

        return VOsaka::spawn($fn());
    }

    public function connect(string $path): Result
    {
        $fn = function () use ($path): Generator {
            $this->validatePath($path);

            $connectTask = function () use ($path): Generator {
                $context = $this->createContext();

                $this->socket = yield @stream_socket_client(
                    "unix://{$path}",
                    $errno,
                    $errstr,
                    30,
                    STREAM_CLIENT_CONNECT,
                    $context
                );
                VOsaka::getLoop()->getGracefulShutdown()->addSocket($this->socket);

                if (!$this->socket) {
                    throw new InvalidArgumentException("Connect failed: $errstr ($errno)");
                }

                $this->configureSocket();
            };

            yield from VOsaka::spawn($connectTask())->unwrap();

            return new UnixStream($this->socket, $path);
        };

        return VOsaka::spawn($fn());
    }

    public function setReuseAddr(bool $reuseAddr): self
    {
        $this->options['reuseaddr'] = $reuseAddr;
        if ($this->socket) {
            socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, $reuseAddr ? 1 : 0);
        }
        return $this;
    }

    private function validatePath(string $path): void
    {
        if (empty($path)) {
            throw new InvalidArgumentException("Unix socket path cannot be empty");
        }

        if (strlen($path) > 108) { // Typical limit for Unix socket path
            throw new InvalidArgumentException("Unix socket path too long (max 108 characters)");
        }
    }

    private function createContext()
    {
        $context = stream_context_create();
        if ($this->options['reuseaddr']) {
            stream_context_set_option($context, 'socket', 'so_reuseaddr', 1);
        }
        return $context;
    }

    private function configureSocket(): void
    {
        if (!$this->socket) {
            return;
        }

        stream_set_blocking($this->socket, false);

        if ($this->options['reuseaddr']) {
            socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1);
        }
    }

    public function getLocalPath(): string
    {
        if (!$this->socket) {
            return '';
        }

        $name = stream_socket_get_name($this->socket, false);
        return $name ?: '';
    }

    public function close(): void
    {
        if ($this->socket) {
            @fclose($this->socket);
            VOsaka::getLoop()->getGracefulShutdown()->cleanup();
            $this->socket = null;
        }

        $this->bound = false;
    }

    public function isClosed(): bool
    {
        return !$this->socket;
    }
}