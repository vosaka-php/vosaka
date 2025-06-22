<?php

declare(strict_types=1);

namespace venndev\vosaka\net\windows;

use Generator;
use InvalidArgumentException;
use Throwable;
use venndev\vosaka\core\interfaces\ISocket;
use venndev\vosaka\time\Sleep;

final class WSock implements ISocket
{
    protected mixed $socket = null;
    protected bool $isConnected = false;
    protected array $eventHandlers = [];
    protected bool $shouldReconnect = false;
    protected int $reconnectAttempts = 0;
    protected int $maxReconnectAttempts = 5;
    protected int $reconnectDelay = 1;
    protected array $options = [];
    protected string $socketType = 'tcp';

    public function __construct(
        protected readonly string $host,
        protected readonly int $port,
        protected readonly int $timeout = 30,
        protected readonly int $bufferSize = 8192,
        array $options = []
    ) {
        if (!$this->isWindowsPlatform()) {
            throw new InvalidArgumentException("WindowsSocket is only supported on Windows platform");
        }

        $this->validateParameters();
        $this->options = array_merge([
            'type' => 'tcp', // tcp, udp, namedpipe
            'pipe_name' => null,
            'security_descriptor' => null,
            'overlapped' => false,
            'completion_port' => false,
            'winsock_version' => '2.2'
        ], $options);

        $this->socketType = $this->options['type'];
    }

    private function isWindowsPlatform(): bool
    {
        return PHP_OS_FAMILY === 'Windows';
    }

    private function validateParameters(): void
    {
        if ($this->options['type'] === 'namedpipe') {
            if (empty($this->options['pipe_name'])) {
                throw new InvalidArgumentException("Named pipe requires 'pipe_name' option");
            }
            return;
        }

        if (!filter_var($this->host, FILTER_VALIDATE_IP) && !filter_var($this->host, FILTER_VALIDATE_DOMAIN)) {
            throw new InvalidArgumentException("Invalid host: {$this->host}");
        }
        if ($this->port < 1 || $this->port > 65535) {
            throw new InvalidArgumentException("Port must be between 1 and 65535: {$this->port}");
        }
    }

    public function connect(): Generator
    {
        if ($this->isConnected) {
            yield "Already connected";
            return true;
        }

        try {
            switch ($this->socketType) {
                case 'tcp':
                    yield from $this->connectTCP();
                    break;
                case 'udp':
                    yield from $this->connectUDP();
                    break;
                case 'namedpipe':
                    yield from $this->connectNamedPipe();
                    break;
                default:
                    throw new InvalidArgumentException("Unsupported socket type: {$this->socketType}");
            }

            $this->isConnected = true;
            $this->reconnectAttempts = 0;

            yield $this->triggerEvent('connected', [
                'type' => $this->socketType,
                'host' => $this->host,
                'port' => $this->port,
                'timestamp' => microtime(true)
            ]);

            return true;

        } catch (Throwable $e) {
            yield $this->triggerEvent('connection_failed', [
                'error' => $e->getMessage(),
                'type' => $this->socketType,
                'host' => $this->host,
                'port' => $this->port
            ]);

            if ($this->shouldReconnect && $this->reconnectAttempts < $this->maxReconnectAttempts) {
                yield from $this->attemptReconnect();
            } else {
                throw new InvalidArgumentException("Connection failed: " . $e->getMessage(), 0, $e);
            }
        }
    }

    private function connectTCP(): Generator
    {
        $this->socket = yield @stream_socket_client("tcp://{$this->host}:{$this->port}", $errno, $errstr, $this->timeout);
        if (!$this->socket) {
            throw new InvalidArgumentException("TCP connection failed: {$errstr} ({$errno})");
        }
        stream_set_timeout($this->socket, $this->timeout);
        return true;
    }

    private function connectUDP(): Generator
    {
        $this->socket = yield @stream_socket_client("udp://{$this->host}:{$this->port}", $errno, $errstr, $this->timeout);
        if (!$this->socket) {
            throw new InvalidArgumentException("UDP connection failed: {$errstr} ({$errno})");
        }
        stream_set_timeout($this->socket, $this->timeout);
        return true;
    }

    private function connectNamedPipe(): Generator
    {
        $pipeName = $this->options['pipe_name'];
        $this->socket = yield @fopen($pipeName, 'r+');
        if (!$this->socket) {
            throw new InvalidArgumentException("Named pipe connection failed: {$pipeName}");
        }
        yield stream_set_timeout($this->socket, $this->timeout);
        return true;
    }

    private function attemptReconnect(): Generator
    {
        $this->reconnectAttempts++;
        yield "Reconnecting ({$this->reconnectAttempts}/{$this->maxReconnectAttempts}) in {$this->reconnectDelay} seconds...";
        yield Sleep::c($this->reconnectDelay);

        try {
            yield from $this->connect();
        } catch (Throwable $e) {
            if ($this->reconnectAttempts < $this->maxReconnectAttempts) {
                yield from $this->attemptReconnect();
            } else {
                throw new InvalidArgumentException("Max reconnect attempts reached: " . $e->getMessage(), 0, $e);
            }
        }
    }

    public function disconnect(): Generator
    {
        if (!$this->isConnected) {
            yield "Socket is not connected";
            return false;
        }

        try {
            if (is_resource($this->socket)) {
                fclose($this->socket);
            }
            $this->isConnected = false;
            $this->socket = null;

            yield $this->triggerEvent('disconnected', [
                'type' => $this->socketType,
                'host' => $this->host,
                'port' => $this->port,
                'timestamp' => microtime(true)
            ]);

            return true;

        } catch (Throwable $e) {
            yield "Disconnection failed: " . $e->getMessage();
            return false;
        }
    }

    public function send(string $data): Generator
    {
        if (!$this->isConnected) {
            yield "Socket is not connected";
            return false;
        }

        try {
            $bytesSent = fwrite($this->socket, $data);
            if ($bytesSent === false) {
                throw new InvalidArgumentException("Failed to send data");
            }

            yield $this->triggerEvent('data_sent', [
                'data' => $data,
                'bytes' => $bytesSent,
                'timestamp' => microtime(true)
            ]);

            return true;

        } catch (Throwable $e) {
            yield "Send failed: " . $e->getMessage();
            return false;
        }
    }

    public function receive(): Generator
    {
        if (!$this->isConnected) {
            yield "Socket is not connected";
            return false;
        }

        try {
            $data = fread($this->socket, $this->bufferSize);
            if ($data === false || $data === '') {
                throw new InvalidArgumentException("Failed to receive data or connection closed");
            }

            yield $this->triggerEvent('data_received', [
                'data' => $data,
                'timestamp' => microtime(true)
            ]);

            return $data;

        } catch (Throwable $e) {
            yield "Receive failed: " . $e->getMessage();
            return false;
        }
    }

    public function on(string $event, callable $handler): void
    {
        if (!isset($this->eventHandlers[$event])) {
            $this->eventHandlers[$event] = [];
        }
        $this->eventHandlers[$event][] = $handler;
    }

    public function off(string $event, ?callable $handler = null): void
    {
        if (!isset($this->eventHandlers[$event])) {
            return;
        }

        if ($handler === null) {
            unset($this->eventHandlers[$event]);
        } else {
            $this->eventHandlers[$event] = array_filter(
                $this->eventHandlers[$event],
                fn($h) => $h !== $handler
            );
        }
    }

    protected function triggerEvent(string $event, array $data): Generator
    {
        if (!isset($this->eventHandlers[$event])) {
            return;
        }

        foreach ($this->eventHandlers[$event] as $handler) {
            yield $handler($data);
        }
    }

    public function enableAutoReconnect(int $maxAttempts = 5, int $delay = 1): void
    {
        $this->shouldReconnect = true;
        $this->maxReconnectAttempts = $maxAttempts;
        $this->reconnectDelay = $delay;
    }

    public function disableAutoReconnect(): void
    {
        $this->shouldReconnect = false;
        $this->maxReconnectAttempts = 0;
        $this->reconnectDelay = 0;
    }

    public function isConnected(): bool
    {
        return $this->isConnected;
    }

    public function getConnectionInfo(): array
    {
        return [
            'type' => $this->socketType,
            'host' => $this->host,
            'port' => $this->port,
            'connected' => $this->isConnected,
            'options' => $this->options
        ];
    }
}
