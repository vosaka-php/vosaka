<?php

declare(strict_types=1);

namespace venndev\vosaka\net\tcp;

use Generator;
use InvalidArgumentException;
use venndev\vosaka\utils\Result;
use venndev\vosaka\VOsaka;

final class UDPSock
{
    private mixed $socket = null;
    private bool $bound = false;
    private string $addr = '';
    private int $port = 0;
    private array $options = [];

    private function __construct(private readonly string $family = 'v4')
    {
        $this->options = [
            'reuseaddr' => true,
            'reuseport' => false,
            'broadcast' => false,
        ];
    }

    public static function newV4(): self
    {
        return new self('v4');
    }

    public static function newV6(): self
    {
        return new self('v6');
    }

    public function bind(string $addr): Result
    {
        $fn = function () use ($addr): Generator {
            [$host, $port] = $this->parseAddr($addr);
            $this->addr = $host;
            $this->port = $port;

            $bindTask = function (): Generator {
                $context = $this->createContext();
                $protocol = $this->family === 'v6' ? 'udp6' : 'udp';

                $this->socket = yield @stream_socket_server(
                    "{$protocol}://{$this->addr}:{$this->port}",
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

    public function sendTo(string $data, string $addr): Result
    {
        if (!$this->socket) {
            throw new InvalidArgumentException("Socket must be created before sending");
        }

        [$host, $port] = $this->parseAddr($addr);

        $sendTask = function () use ($data, $host, $port): Generator {
            $result = yield @stream_socket_sendto(
                $this->socket,
                $data,
                0,
                "{$host}:{$port}"
            );

            if ($result === false || $result === -1) {
                $error = error_get_last();
                throw new InvalidArgumentException("Send failed: " . ($error['message'] ?? 'Unknown error'));
            }

            return $result;
        };

        return VOsaka::spawn($sendTask());
    }

    public function receiveFrom(int $maxLength = 65535): Result
    {
        if (!$this->bound) {
            throw new InvalidArgumentException("Socket must be bound before receiving");
        }

        $receiveTask = function () use ($maxLength): Generator {
            $data = yield @stream_socket_recvfrom($this->socket, $maxLength, 0, $peerAddr);

            if ($data === false) {
                $error = error_get_last();
                throw new InvalidArgumentException("Receive failed: " . ($error['message'] ?? 'Unknown error'));
            }

            return ['data' => $data, 'peerAddr' => $peerAddr];
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

    public function setReusePort(bool $reusePort): self
    {
        $this->options['reuseport'] = $reusePort;

        if ($this->socket) {
            socket_set_option($this->socket, SOL_SOCKET, SO_REUSEPORT, $reusePort ? 1 : 0);
        }

        return $this;
    }

    public function setBroadcast(bool $broadcast): self
    {
        $this->options['broadcast'] = $broadcast;
        if ($this->socket) {
            socket_set_option($this->socket, SOL_SOCKET, SO_BROADCAST, $broadcast ? 1 : 0);
        }

        return $this;
    }

    private function parseAddr(string $addr): array
    {
        if (strpos($addr, ':') === false) {
            throw new InvalidArgumentException("Invalid address format. Expected 'host:port'");
        }

        $parts = explode(':', $addr);
        $port = (int) array_pop($parts);
        $host = implode(':', $parts);

        if ($port < 1 || $port > 65535) {
            throw new InvalidArgumentException("Port must be between 1 and 65535: {$port}");
        }

        return [$host, $port];
    }

    private function createContext()
    {
        $context = stream_context_create();

        if ($this->options['reuseaddr']) {
            stream_context_set_option($context, 'socket', 'so_reuseaddr', 1);
        }

        if ($this->options['reuseport']) {
            stream_context_set_option($context, 'socket', 'so_reuseport', 1);
        }

        if ($this->options['broadcast']) {
            stream_context_set_option($context, 'socket', 'so_broadcast', 1);
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

        if ($this->options['reuseport']) {
            socket_set_option($this->socket, SOL_SOCKET, SO_REUSEPORT, 1);
        }

        if ($this->options['broadcast']) {
            socket_set_option($this->socket, SOL_SOCKET, SO_BROADCAST, 1);
        }
    }

    public function getLocalAddr(): string
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