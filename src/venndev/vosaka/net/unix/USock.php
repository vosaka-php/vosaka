<?php

declare(strict_types=1);

namespace venndev\vosaka\net\unix;

use Generator;
use InvalidArgumentException;
use Throwable;
use venndev\vosaka\core\interfaces\ISocket;
use venndev\vosaka\time\Sleep;

final class USock implements ISocket
{
    protected mixed $socket = null;
    protected bool $isConnected = false;
    protected array $eventHandlers = [];
    protected bool $shouldReconnect = false;
    protected int $reconnectAttempts = 0;
    protected int $maxReconnectAttempts = 5;
    protected int $reconnectDelay = 1;
    protected array $options = [];
    protected bool $isServer = false;

    public function __construct(
        protected readonly string $socketPath,
        protected readonly int $timeout = 30,
        protected readonly int $bufferSize = 8192,
        array $options = []
    ) {
        if (!$this->isUnixSocketSupported()) {
            throw new InvalidArgumentException("Unix domain sockets are not supported on this platform");
        }

        $this->validateParameters();
        $this->options = array_merge([
            'permissions' => 0666,
            'auto_unlink' => true,
            'credentials' => false,
            'abstract' => false, // Abstract namespace (Linux)
            'type' => SOCK_STREAM // SOCK_STREAM or SOCK_DGRAM
        ], $options);
    }

    private function isUnixSocketSupported(): bool
    {
        return function_exists('socket_create') && defined('AF_UNIX');
    }

    private function validateParameters(): void
    {
        if (empty($this->socketPath)) {
            throw new InvalidArgumentException("Socket path cannot be empty");
        }

        // Check if path is valid for Unix socket
        if (!$this->options['abstract'] && strlen($this->socketPath) > 107) {
            throw new InvalidArgumentException("Unix socket path too long (max 107 characters)");
        }

        // For abstract sockets (Linux), path should start with null byte
        if ($this->options['abstract'] && !str_starts_with($this->socketPath, "\0")) {
            $this->socketPath = "\0" . $this->socketPath;
        }
    }

    public function connect(): Generator
    {
        if ($this->isConnected) {
            yield "Already connected to {$this->socketPath}";
            return true;
        }

        try {
            if ($this->options['type'] === SOCK_STREAM) {
                yield from $this->connectStream();
            } else {
                yield from $this->connectDatagram();
            }

            $this->isConnected = true;
            $this->reconnectAttempts = 0;

            yield $this->triggerEvent('connected', [
                'path' => $this->socketPath,
                'type' => $this->options['type'] === SOCK_STREAM ? 'stream' : 'datagram',
                'timestamp' => microtime(true)
            ]);

            return true;

        } catch (Throwable $e) {
            yield $this->triggerEvent('connection_failed', [
                'error' => $e->getMessage(),
                'path' => $this->socketPath
            ]);

            if ($this->shouldReconnect && $this->reconnectAttempts < $this->maxReconnectAttempts) {
                yield from $this->attemptReconnect();
            }

            throw $e;
        }
    }

    private function connectStream(): Generator
    {
        $this->socket = @stream_socket_client(
            "unix://{$this->socketPath}",
            $errno,
            $errstr,
            $this->timeout
        );

        if (!$this->socket) {
            throw new InvalidArgumentException("Failed to connect to Unix socket {$this->socketPath} - $errstr ($errno)");
        }

        stream_set_timeout($this->socket, $this->timeout);
        stream_set_blocking($this->socket, false);

        yield "Connected to Unix stream socket: {$this->socketPath}";
    }

    private function connectDatagram(): Generator
    {
        $this->socket = socket_create(AF_UNIX, SOCK_DGRAM, 0);

        if (!$this->socket) {
            throw new InvalidArgumentException("Failed to create Unix datagram socket: " . socket_strerror(socket_last_error()));
        }

        socket_set_nonblock($this->socket);

        if (!socket_connect($this->socket, $this->socketPath)) {
            $error = socket_strerror(socket_last_error($this->socket));
            socket_close($this->socket);
            throw new InvalidArgumentException("Failed to connect to Unix datagram socket {$this->socketPath}: {$error}");
        }

        yield "Connected to Unix datagram socket: {$this->socketPath}";
    }

    public function disconnect(): Generator
    {
        if (!$this->isConnected) {
            yield "No active connection to close.";
            return;
        }

        try {
            if ($this->socket) {
                if ($this->options['type'] === SOCK_STREAM) {
                    fclose($this->socket);
                } else {
                    socket_close($this->socket);
                }
                $this->socket = null;
            }

            // Clean up socket file if we created it and auto_unlink is enabled
            if ($this->isServer && $this->options['auto_unlink'] && !$this->options['abstract']) {
                @unlink($this->socketPath);
            }

            $this->isConnected = false;
            $this->shouldReconnect = false;

            yield $this->triggerEvent('disconnected', [
                'path' => $this->socketPath,
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
            if ($this->options['type'] === SOCK_STREAM) {
                $bytesWritten = @fwrite($this->socket, $data);
            } else {
                $bytesWritten = socket_send($this->socket, $data, strlen($data), 0);
            }

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

    public function receive(): Generator
    {
        if (!$this->isConnected) {
            throw new InvalidArgumentException("Not connected to any socket");
        }

        try {
            if ($this->options['type'] === SOCK_STREAM) {
                $data = yield from $this->receiveStream();
            } else {
                $data = yield from $this->receiveDatagram();
            }

            if ($data === false || $data === '') {
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

    private function receiveStream(): Generator
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

    private function receiveDatagram(): Generator
    {
        $data = '';
        $bytesReceived = yield socket_recv($this->socket, $data, $this->bufferSize, MSG_DONTWAIT);

        if ($bytesReceived === false) {
            $error = yield socket_last_error($this->socket);
            if ($error === SOCKET_EAGAIN || $error === SOCKET_EWOULDBLOCK) {
                return null;
            }
            throw new InvalidArgumentException("Failed to receive data: " . socket_strerror($error));
        }

        return $data;
    }

    public function handleUnix(): Generator
    {
        if (!$this->isConnected) {
            yield from $this->connect();
        }

        while ($this->isConnected) {
            try {
                $data = yield from $this->receive();

                if ($data === null) {
                    yield Sleep::c(0.01);
                    continue;
                }

                if ($data === false) {
                    yield $this->triggerEvent('connection_lost', "Connection lost");
                    if ($this->shouldReconnect) {
                        yield from $this->attemptReconnect();
                        continue;
                    }
                    break;
                }

                // Process received data
                yield $this->processData($data);

            } catch (Throwable $e) {
                yield $this->triggerEvent('error', [
                    'type' => 'unix_handling_error',
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

    public function sendCredentials(): Generator
    {
        if (!$this->options['credentials']) {
            throw new InvalidArgumentException("Credentials passing not enabled");
        }

        if ($this->options['type'] !== SOCK_STREAM) {
            throw new InvalidArgumentException("Credentials can only be sent over stream sockets");
        }

        // Send process credentials (PID, UID, GID)
        $pid = getmypid();
        $uid = getmyuid();
        $gid = getmygid();

        $credentials = json_encode([
            'pid' => $pid,
            'uid' => $uid,
            'gid' => $gid,
            'timestamp' => microtime(true)
        ]);

        yield from $this->send($credentials);
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

    public function createServer(): Generator
    {
        if ($this->isConnected) {
            throw new InvalidArgumentException("Socket already connected/created");
        }

        try {
            // Remove existing socket file if it exists and not abstract
            if (!$this->options['abstract'] && file_exists($this->socketPath)) {
                if ($this->options['auto_unlink']) {
                    unlink($this->socketPath);
                } else {
                    throw new InvalidArgumentException("Socket file already exists: {$this->socketPath}");
                }
            }

            if ($this->options['type'] === SOCK_STREAM) {
                yield from $this->createStreamServer();
            } else {
                yield from $this->createDatagramServer();
            }

            // Set permissions for non-abstract sockets
            if (!$this->options['abstract'] && file_exists($this->socketPath)) {
                chmod($this->socketPath, $this->options['permissions']);
            }

            $this->isConnected = true;
            $this->isServer = true;

            yield $this->triggerEvent('server_created', [
                'path' => $this->socketPath,
                'type' => $this->options['type'] === SOCK_STREAM ? 'stream' : 'datagram',
                'timestamp' => microtime(true)
            ]);

            return true;

        } catch (Throwable $e) {
            yield $this->triggerEvent('server_creation_failed', [
                'error' => $e->getMessage(),
                'path' => $this->socketPath
            ]);
            throw $e;
        }
    }

    private function createStreamServer(): Generator
    {
        $this->socket = @stream_socket_server(
            "unix://{$this->socketPath}",
            $errno,
            $errstr,
            STREAM_SERVER_BIND | STREAM_SERVER_LISTEN
        );

        if (!$this->socket) {
            throw new InvalidArgumentException("Failed to create Unix stream server {$this->socketPath} - $errstr ($errno)");
        }

        stream_set_blocking($this->socket, false);
        yield "Created Unix stream server: {$this->socketPath}";
    }

    private function createDatagramServer(): Generator
    {
        $this->socket = socket_create(AF_UNIX, SOCK_DGRAM, 0);

        if (!$this->socket) {
            throw new InvalidArgumentException("Failed to create Unix datagram server: " . socket_strerror(socket_last_error()));
        }

        socket_set_nonblock($this->socket);

        if (!socket_bind($this->socket, $this->socketPath)) {
            $error = socket_strerror(socket_last_error($this->socket));
            socket_close($this->socket);
            throw new InvalidArgumentException("Failed to bind Unix datagram server {$this->socketPath}: {$error}");
        }

        yield "Created Unix datagram server: {$this->socketPath}";
    }

    public function acceptConnection(): Generator
    {
        if (!$this->isServer || $this->options['type'] !== SOCK_STREAM) {
            throw new InvalidArgumentException("Can only accept connections on stream server sockets");
        }

        $clientSocket = @stream_socket_accept($this->socket, $this->timeout);

        if ($clientSocket === false) {
            return null;
        }

        stream_set_blocking($clientSocket, false);

        $client = new self($this->socketPath, $this->timeout, $this->bufferSize, $this->options);
        $client->socket = $clientSocket;
        $client->isConnected = true;

        yield $this->triggerEvent('client_connected', [
            'client' => $client,
            'timestamp' => microtime(true)
        ]);

        return $client;
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
            'path' => $this->socketPath,
            'protocol' => 'unix',
            'type' => $this->options['type'] === SOCK_STREAM ? 'stream' : 'datagram',
            'timeout' => $this->timeout,
            'buffer_size' => $this->bufferSize,
            'connected' => $this->isConnected,
            'is_server' => $this->isServer,
            'auto_reconnect' => $this->shouldReconnect,
            'reconnect_attempts' => $this->reconnectAttempts,
            'max_reconnect_attempts' => $this->maxReconnectAttempts,
            'options' => $this->options
        ];
    }
}