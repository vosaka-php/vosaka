<?php

declare(strict_types=1);

namespace venndev\vosaka\net\unix;

use Generator;
use InvalidArgumentException;
use venndev\vosaka\core\Result;
use venndev\vosaka\core\Future;
use venndev\vosaka\net\SocketBase;
use venndev\vosaka\VOsaka;

final class UnixDatagram extends SocketBase
{
    private bool $bound = false;
    private string $path = "";

    public static function bind(string $path, array $options = []): Result
    {
        $fn = function () use ($path, $options): Generator {
            yield;
            self::validatePath($path);
            $this->path = $path;
            $this->options = array_merge(["reuseaddr" => true], $options);

            if (file_exists($path)) {
                unlink($path);
            }

            $context = self::createContext($this->options);

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

            self::addToEventLoop($this->socket);
            self::applySocketOptions($this->socket, $this->options);
            $this->bound = true;

            return $this;
        };

        return Future::new($fn());
    }

    public function sendTo(string $data, string $path): Result
    {
        $fn = function () use ($data, $path): Generator {
            yield;
            self::validatePath($path);

            if (!$this->socket) {
                $context = self::createContext($this->options);
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

                self::addToEventLoop($this->socket);
                self::applySocketOptions($this->socket, $this->options);
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

    public function localPath(): string
    {
        if (!$this->socket) {
            return "";
        }

        $name = stream_socket_get_name($this->socket, false);
        return $name ?: "";
    }

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

    public function isClosed(): bool
    {
        return !$this->socket;
    }
}
