<?php

declare(strict_types=1);

namespace venndev\vosaka\net\tcp;

use Generator;
use InvalidArgumentException;
use Throwable;
use venndev\vosaka\core\interfaces\ISocket;
use venndev\vosaka\time\Sleep;
use venndev\vosaka\VOsaka;

final class TCPSock implements ISocket
{
    protected mixed $socket = null;
    protected bool $isConnected = false;
    protected array $eventHandlers = [];
    protected bool $shouldReconnect = false;
    protected int $reconnectAttempts = 0;
    protected int $maxReconnectAttempts = 5;
    protected int $reconnectDelay = 1;
    protected array $options = [];

    public function __construct(
        protected readonly string $host,
        protected readonly int $port,
        protected readonly int $timeout = 30,
        protected readonly int $bufferSize = 8192,
        array $options = []
    ) {
        $this->validateParameters();
        $this->options = array_merge([
            'keepalive' => true,
            'nodelay' => true,
            'reuseaddr' => true,
            'ssl' => false,
            'ssl_verify' => true
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
            $protocol = $this->options['ssl'] ? 'ssl' : 'tcp';
            $context = $this->createContext();

            $this->socket = @stream_socket_client(
                "{$protocol}://{$this->host}:{$this->port}",
                $errno,
                $errstr,
                $this->timeout,
                STREAM_CLIENT_CONNECT,
                $context
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
                'protocol' => $protocol,
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

    private function createContext()
    {
        $context = stream_context_create();

        if ($this->options['ssl']) {
            stream_context_set_option($context, 'ssl', 'verify_peer', $this->options['ssl_verify']);
            stream_context_set_option($context, 'ssl', 'verify_peer_name', $this->options['ssl_verify']);
        }

        return $context;
    }

    private function configureSocket(): void
    {
        stream_set_timeout($this->socket, $this->timeout);
        stream_set_blocking($this->socket, false);

        if ($this->options['keepalive']) {
            socket_set_option($this->socket, SOL_SOCKET, SO_KEEPALIVE, 1);
        }

        if ($this->options['nodelay']) {
            socket_set_option($this->socket, SOL_TCP, TCP_NODELAY, 1);
        }
    }

    public function disconnect(): Generator
    {
        if (!$this->isConnected) {
            yield "No active connection to close.";
            return;
        }

        try {
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

    public function send(string $data): Generator
    {
        if (!$this->isConnected) {
            throw new InvalidArgumentException("Not connected to any socket");
        }

        try {
            $totalBytes = strlen($data);
            $bytesWritten = 0;

            while ($bytesWritten < $totalBytes) {
                $result = @fwrite($this->socket, substr($data, $bytesWritten));

                if ($result === false) {
                    throw new InvalidArgumentException("Failed to send data");
                }

                $bytesWritten += $result;

                if ($bytesWritten < $totalBytes) {
                    yield Sleep::c(0.001);
                }
            }

            yield $this->triggerEvent('data_sent', [
                'bytes' => $bytesWritten,
                'total' => $totalBytes,
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

    public function receive(): Generator
    {
        if (!$this->isConnected) {
            throw new InvalidArgumentException("Not connected to any socket");
        }

        try {
            $data = yield from $this->readDataAsync();

            if ($data === false || $data === '') {
                if (feof($this->socket)) {
                    yield $this->triggerEvent('connection_lost', "Connection lost");

                    if ($this->shouldReconnect) {
                        yield from $this->attemptReconnect();
                        return null;
                    }
                    return false;
                }
                return null;
            }

            yield $this->triggerEvent('data_received', [
                'data' => $data,
                'length' => strlen($data),
                'timestamp' => microtime(true)
            ]);

            return $data;

        } catch (Throwable $e) {
            yield $this->triggerEvent('error', [
                'type' => 'receive_error',
                'message' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function readDataAsync(): Generator
    {
        $startTime = microtime(true);

        while (microtime(true) - $startTime < $this->timeout) {
            $data = @fread($this->socket, $this->bufferSize);

            if ($data !== false && $data !== '') {
                return $data;
            }

            if (feof($this->socket)) {
                return false;
            }

            yield Sleep::c(0.001);
        }

        throw new InvalidArgumentException("Read timeout exceeded");
    }

    public function handleTCP(): Generator
    {
        if (!$this->isConnected) {
            yield from $this->connect();
        }

        while ($this->isConnected) {
            try {
                $data = yield from $this->receive();

                if ($data === false) {
                    break;
                }

                if ($data === null) {
                    yield Sleep::c(0.01);
                    continue;
                }

                // Process received data
                yield $this->processData($data);

            } catch (Throwable $e) {
                yield $this->triggerEvent('error', [
                    'type' => 'tcp_handling_error',
                    'message' => $e->getMessage()
                ]);
                break;
            }
        }
    }

    protected function processData(string $data): string
    {
        // Override in subclasses for protocol-specific processing
        return $data;
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

    public function ping(): Generator
    {
        $startTime = microtime(true);

        try {
            yield from $this->send("PING\n");
            $response = yield from $this->receive();
            $endTime = microtime(true);

            $latency = ($endTime - $startTime) * 1000;

            yield $this->triggerEvent('ping_response', [
                'response' => $response,
                'latency' => $latency,
                'timestamp' => $endTime
            ]);

            return $latency;

        } catch (Throwable $e) {
            yield $this->triggerEvent('ping_failed', [
                'error' => $e->getMessage()
            ]);
            throw $e;
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

    public function getConnectionInfo(): array
    {
        return [
            'host' => $this->host,
            'port' => $this->port,
            'protocol' => 'tcp',
            'timeout' => $this->timeout,
            'buffer_size' => $this->bufferSize,
            'connected' => $this->isConnected,
            'auto_reconnect' => $this->shouldReconnect,
            'reconnect_attempts' => $this->reconnectAttempts,
            'max_reconnect_attempts' => $this->maxReconnectAttempts,
            'options' => $this->options
        ];
    }

    public static function connectMultiple(array $sockets): Generator
    {
        $tasks = [];

        foreach ($sockets as $socket) {
            if (!$socket instanceof self) {
                throw new InvalidArgumentException("All items must be TCPSocket instances");
            }
            $tasks[] = $socket->connect();
        }

        yield VOsaka::join(...$tasks);
    }

    public static function raceConnect(array $sockets): Generator
    {
        $tasks = [];

        foreach ($sockets as $socket) {
            if (!$socket instanceof self) {
                throw new InvalidArgumentException("All items must be TCPSocket instances");
            }
            $tasks[] = $socket->connect();
        }

        return yield VOsaka::select(...$tasks);
    }
}