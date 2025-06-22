<?php

declare(strict_types=1);

namespace venndev\vosaka\net\udp;

use Generator;
use InvalidArgumentException;
use Throwable;
use venndev\vosaka\core\interfaces\ISocket;
use venndev\vosaka\time\Sleep;

if (!defined('IP_DROP_MEMBERSHIP')) {
    define('IP_DROP_MEMBERSHIP', 12); // Define the constant manually if missing
}

if (!defined('IP_ADD_MEMBERSHIP')) {
    define('IP_ADD_MEMBERSHIP', 13); // Define the constant manually if missing
}

final class UDPSock implements ISocket
{
    protected mixed $socket = null;
    protected bool $isConnected = false;
    protected array $eventHandlers = [];
    protected bool $shouldReconnect = false;
    protected int $reconnectAttempts = 0;
    protected int $maxReconnectAttempts = 5;
    protected int $reconnectDelay = 1;
    protected array $options = [];
    protected ?string $lastPeer = null;

    public function __construct(
        protected readonly string $host,
        protected readonly int $port,
        protected readonly int $timeout = 30,
        protected readonly int $bufferSize = 8192,
        array $options = []
    ) {
        $this->validateParameters();
        $this->options = array_merge([
            'broadcast' => false,
            'multicast' => false,
            'multicast_group' => null,
            'multicast_interface' => null,
            'reuseaddr' => true,
            'bind_to_device' => null
        ], $options);
    }

    private function validateParameters(): void
    {
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
            yield "Already connected to {$this->host}:{$this->port}";
            return true;
        }

        try {
            $this->socket = @stream_socket_client(
                "udp://{$this->host}:{$this->port}",
                $errno,
                $errstr,
                $this->timeout
            );

            if (!$this->socket) {
                throw new InvalidArgumentException("Failed to connect to {$this->host}:{$this->port} - $errstr ($errno)");
            }

            $this->configureSocket();
            $this->isConnected = true;
            $this->reconnectAttempts = 0;

            yield $this->triggerEvent('connected', [
                'host' => $this->host,
                'port' => $this->port,
                'protocol' => 'udp',
                'timestamp' => microtime(true)
            ]);

            return true;

        } catch (Throwable $e) {
            yield $this->triggerEvent('connection_failed', [
                'error' => $e->getMessage(),
                'host' => $this->host,
                'port' => $this->port
            ]);

            if ($this->shouldReconnect && $this->reconnectAttempts < $this->maxReconnectAttempts) {
                yield from $this->attemptReconnect();
            }

            throw $e;
        }
    }

    private function configureSocket(): void
    {
        stream_set_timeout($this->socket, $this->timeout);
        stream_set_blocking($this->socket, false);

        if ($this->options['broadcast']) {
            socket_set_option($this->socket, SOL_SOCKET, SO_BROADCAST, 1);
        }

        if ($this->options['reuseaddr']) {
            socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1);
        }

        if ($this->options['multicast'] && $this->options['multicast_group']) {
            $this->setupMulticast();
        }
    }

    private function setupMulticast(): void
    {
        $group = $this->options['multicast_group'];
        $interface = $this->options['multicast_interface'] ?? '0.0.0.0';

        // Join multicast group
        $mreq = pack('a4a4', inet_pton($group), inet_pton($interface));
        socket_set_option($this->socket, IPPROTO_IP, IP_ADD_MEMBERSHIP, $mreq);

        // Set multicast interface
        socket_set_option($this->socket, IPPROTO_IP, IP_MULTICAST_IF, inet_pton($interface));

        // Set multicast TTL
        socket_set_option($this->socket, IPPROTO_IP, IP_MULTICAST_TTL, 1);
    }

    public function disconnect(): Generator
    {
        if (!$this->isConnected) {
            yield "No active connection to close.";
            return;
        }

        try {
            if ($this->options['multicast'] && $this->options['multicast_group']) {
                $this->leaveMulticastGroup();
            }

            if ($this->socket) {
                fclose($this->socket);
                $this->socket = null;
            }

            $this->isConnected = false;
            $this->shouldReconnect = false;

            yield $this->triggerEvent('disconnected', [
                'host' => $this->host,
                'port' => $this->port,
                'timestamp' => microtime(true)
            ]);

        } catch (Throwable $e) {
            yield $this->triggerEvent('error', [
                'type' => 'disconnect_error',
                'message' => $e->getMessage()
            ]);
        }
    }

    private function leaveMulticastGroup(): void
    {
        $group = $this->options['multicast_group'];
        $interface = $this->options['multicast_interface'] ?? '0.0.0.0';

        $mreq = pack('a4a4', inet_pton($group), inet_pton($interface));



        socket_set_option($this->socket, IPPROTO_IP, IP_DROP_MEMBERSHIP, $mreq);
    }

    public function send(string $data): Generator
    {
        if (!$this->isConnected) {
            throw new InvalidArgumentException("Not connected to any socket");
        }

        try {
            $bytesWritten = @fwrite($this->socket, $data);

            if ($bytesWritten === false) {
                throw new InvalidArgumentException("Failed to send data");
            }

            yield $this->triggerEvent('data_sent', [
                'bytes' => $bytesWritten,
                'total' => strlen($data),
                'timestamp' => microtime(true)
            ]);

            return $bytesWritten;

        } catch (Throwable $e) {
            yield $this->triggerEvent('error', [
                'type' => 'send_error',
                'message' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function sendTo(string $data, string $host, int $port): Generator
    {
        if (!$this->isConnected) {
            throw new InvalidArgumentException("Not connected to any socket");
        }

        try {
            $peer = "udp://{$host}:{$port}";
            $bytesWritten = @stream_socket_sendto($this->socket, $data, 0, $peer);

            if ($bytesWritten === false) {
                throw new InvalidArgumentException("Failed to send data to {$peer}");
            }

            yield $this->triggerEvent('data_sent_to', [
                'bytes' => $bytesWritten,
                'total' => strlen($data),
                'peer' => $peer,
                'timestamp' => microtime(true)
            ]);

            return $bytesWritten;

        } catch (Throwable $e) {
            yield $this->triggerEvent('error', [
                'type' => 'send_to_error',
                'message' => $e->getMessage(),
                'peer' => "{$host}:{$port}"
            ]);
            throw $e;
        }
    }

    public function receive(): Generator
    {
        if (!$this->isConnected) {
            throw new InvalidArgumentException("Not connected to any socket");
        }

        try {
            $data = @stream_socket_recvfrom($this->socket, $this->bufferSize, 0, $peer);

            if ($data === false || $data === '') {
                return null;
            }

            $this->lastPeer = $peer;

            yield $this->triggerEvent('data_received', [
                'data' => $data,
                'length' => strlen($data),
                'peer' => $peer,
                'timestamp' => microtime(true)
            ]);

            return ['data' => $data, 'peer' => $peer];

        } catch (Throwable $e) {
            yield $this->triggerEvent('error', [
                'type' => 'receive_error',
                'message' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function handleUDP(): Generator
    {
        if (!$this->isConnected) {
            yield from $this->connect();
        }

        while ($this->isConnected) {
            try {
                $result = yield from $this->receive();

                if ($result === null) {
                    yield Sleep::c(0.01);
                    continue;
                }

                // Process received data
                yield $this->processData($result['data'], $result['peer']);

            } catch (Throwable $e) {
                yield $this->triggerEvent('error', [
                    'type' => 'udp_handling_error',
                    'message' => $e->getMessage()
                ]);
                break;
            }
        }
    }

    protected function processData(string $data, string $peer): array
    {
        // Override in subclasses for protocol-specific processing
        return ['data' => $data, 'peer' => $peer];
    }

    public function broadcast(string $data, int $port = null): Generator
    {
        if (!$this->options['broadcast']) {
            throw new InvalidArgumentException("Broadcasting not enabled. Set 'broadcast' => true in options.");
        }

        $broadcastPort = $port ?? $this->port;
        yield from $this->sendTo($data, '255.255.255.255', $broadcastPort);
    }

    public function multicast(string $data): Generator
    {
        if (!$this->options['multicast'] || !$this->options['multicast_group']) {
            throw new InvalidArgumentException("Multicast not configured properly.");
        }

        yield from $this->sendTo($data, $this->options['multicast_group'], $this->port);
    }

    private function attemptReconnect(): Generator
    {
        $this->reconnectAttempts++;
        $delay = min($this->reconnectDelay * pow(2, $this->reconnectAttempts - 1), 60);

        yield $this->triggerEvent('reconnecting', [
            'attempt' => $this->reconnectAttempts,
            'max_attempts' => $this->maxReconnectAttempts,
            'delay' => $delay
        ]);

        yield Sleep::c($delay);

        try {
            $this->isConnected = false;
            yield from $this->connect();
        } catch (Throwable $e) {
            yield $this->triggerEvent('reconnect_failed', [
                'attempt' => $this->reconnectAttempts,
                'error' => $e->getMessage()
            ]);

            if ($this->reconnectAttempts >= $this->maxReconnectAttempts) {
                $this->shouldReconnect = false;
                throw new InvalidArgumentException("Max reconnection attempts reached");
            }
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
            return;
        }

        $this->eventHandlers[$event] = array_filter(
            $this->eventHandlers[$event],
            fn($h) => $h !== $handler
        );
    }

    private function triggerEvent(string $event, mixed $data = null): string
    {
        if (isset($this->eventHandlers[$event])) {
            foreach ($this->eventHandlers[$event] as $handler) {
                $handler($data, $this);
            }
        }
        return is_array($data) ? json_encode($data) : $data;
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
    }

    public function isConnected(): bool
    {
        return $this->isConnected;
    }

    public function getSocket(): mixed
    {
        return $this->socket;
    }

    public function getLastPeer(): ?string
    {
        return $this->lastPeer;
    }

    public function getConnectionInfo(): array
    {
        return [
            'host' => $this->host,
            'port' => $this->port,
            'protocol' => 'udp',
            'timeout' => $this->timeout,
            'buffer_size' => $this->bufferSize,
            'connected' => $this->isConnected,
            'auto_reconnect' => $this->shouldReconnect,
            'reconnect_attempts' => $this->reconnectAttempts,
            'max_reconnect_attempts' => $this->maxReconnectAttempts,
            'options' => $this->options,
            'last_peer' => $this->lastPeer
        ];
    }

    public static function createServer(string $host, int $port, array $options = []): Generator
    {
        $context = stream_context_create([
            'socket' => [
                'so_reuseport' => true,
                'so_broadcast' => $options['broadcast'] ?? false
            ]
        ]);

        $socket = @stream_socket_server("udp://{$host}:{$port}", $errno, $errstr, STREAM_SERVER_BIND, $context);

        if (!$socket) {
            throw new InvalidArgumentException("Failed to create UDP server on {$host}:{$port} - $errstr ($errno)");
        }

        $server = new self($host, $port, $options['timeout'] ?? 30, $options['buffer_size'] ?? 8192, $options);
        $server->socket = $socket;
        $server->isConnected = true;

        yield $server->triggerEvent('server_created', [
            'host' => $host,
            'port' => $port,
            'timestamp' => microtime(true)
        ]);

        return $server;
    }
}