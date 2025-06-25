<?php

declare(strict_types=1);

namespace venndev\vosaka\net\unix;

use Generator;
use InvalidArgumentException;
use venndev\vosaka\core\Result;
use venndev\vosaka\VOsaka;

final class UnixDatagram
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
                    "udg://{$this->path}",
                    $errno,
                    $errstr,
                    STREAM_SERVER_BIND,
                    $context
                );

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

    /**
     * Send data to a specific Unix socket path
     */
    public function sendTo(string $data, string $path): Result
    {
        if (!$this->socket) {
            throw new InvalidArgumentException("Socket must be created before sending");
        }

        $this->validatePath($path);

        $sendTask = function () use ($data, $path): Generator {
            $result = yield @stream_socket_sendto(
                $this->socket,
                $data,
                0,
                $path
            );

            if ($result === false || $result === -1) {
                $error = error_get_last();
                throw new InvalidArgumentException("Send failed: " . ($error['message'] ?? 'Unknown error'));
            }

            return $result;
        };

        return VOsaka::spawn($sendTask());
    }

    /**
     * Receive data from a Unix socket
     */
    public function receiveFrom(int $maxLength = 65535): Result
    {
        if (!$this->bound) {
            throw new InvalidArgumentException("Socket must be bound before receiving");
        }

        $receiveTask = function () use ($maxLength): Generator {
            $data = yield @stream_socket_recvfrom($this->socket, $maxLength, 0, $peerPath);

            if ($data === false) {
                $error = error_get_last();
                throw new InvalidArgumentException("Receive failed: " . ($error['message'] ?? 'Unknown error'));
            }

            return ['data' => $data, 'peerPath' => $peerPath];
        };

        return VOsaka::spawn($receiveTask());
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

        if (strlen($path) > 108) {
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
            $this->socket = null;
        }
        $this->bound = false;
    }

    public function isClosed(): bool
    {
        return !$this->socket;
    }
}