<?php

declare(strict_types=1);

namespace venndev\vosaka\net\tcp;

use Generator;
use InvalidArgumentException;
use Throwable;
use venndev\vosaka\utils\Defer;
use venndev\vosaka\core\Result;
use venndev\vosaka\VOsaka;

final class TCPListener
{
    private mixed $socket = null;
    private bool $isListening = false;
    private array $options = [];

    private function __construct(
        private readonly string $host,
        private readonly int $port,
        array $options = []
    ) {
        $this->options = array_merge([
            'reuseaddr' => true,
            'backlog' => SOMAXCONN,
            'ssl' => false,
            'ssl_cert' => null,
            'ssl_key' => null,
        ], $options);
    }

    /**
     * Create a new TCP listener
     * @param string $addr Address in 'host:port' format
     * @param array $options Additional options like 'ssl', 'ssl_cert', 'ssl_key'
     * @return Result<TCPListener>
     */
    public static function bind(string $addr, array $options = []): Result
    {
        $fn = function () use ($addr, $options): Generator {
            $parts = explode(':', $addr);
            if (count($parts) !== 2) {
                throw new InvalidArgumentException("Invalid address format. Use 'host:port'");
            }

            $host = $parts[0];
            $port = (int) $parts[1];

            if ($port < 1 || $port > 65535) {
                throw new InvalidArgumentException("Port must be between 1 and 65535");
            }

            $listener = new self($host, $port, $options);
            yield from $listener->bindSocket()->unwrap();

            return $listener;
        };

        return VOsaka::spawn($fn());
    }

    /**
     * Bind the socket to the specified address and port
     * @return Result<void>
     */
    private function bindSocket(): Result
    {
        $fn = function (): Generator {
            try {
                $protocol = $this->options['ssl'] ? 'ssl' : 'tcp';
                $context = $this->createContext();

                $this->socket = @stream_socket_server(
                    "{$protocol}://{$this->host}:{$this->port}",
                    $errno,
                    $errstr,
                    STREAM_SERVER_BIND | STREAM_SERVER_LISTEN,
                    $context
                );

                if (!$this->socket) {
                    throw new InvalidArgumentException("Failed to bind to {$this->host}:{$this->port}: $errstr");
                }

                yield stream_set_blocking($this->socket, false);
                $this->isListening = true;
            } catch (Throwable $e) {
                throw new InvalidArgumentException("Bind failed: " . $e->getMessage());
            }
        };

        return VOsaka::spawn($fn());
    }

    private function createContext()
    {
        $context = stream_context_create();

        if ($this->options['ssl']) {
            if (!$this->options['ssl_cert'] || !$this->options['ssl_key']) {
                throw new InvalidArgumentException("SSL certificate and key required for SSL");
            }

            stream_context_set_option($context, 'ssl', 'local_cert', $this->options['ssl_cert']);
            stream_context_set_option($context, 'ssl', 'local_pk', $this->options['ssl_key']);
            stream_context_set_option($context, 'ssl', 'verify_peer', false);
            stream_context_set_option($context, 'ssl', 'allow_self_signed', true);
        }

        if ($this->options['reuseaddr']) {
            stream_context_set_option($context, 'socket', 'so_reuseport', 1);
        }

        return $context;
    }

    /**
     * Accept incoming connections
     * @return Result<TCPStream>
     */
    public function accept(): Result
    {
        $fn = function (): Generator {
            if (!$this->isListening) {
                throw new InvalidArgumentException("Listener is not bound");
            }

            while (true) {
                $clientSocket = @stream_socket_accept($this->socket, 0, $peerName);

                if ($clientSocket) {
                    stream_set_blocking($clientSocket, false);
                    return new TCPStream($clientSocket, $peerName);
                }

                yield;
            }
        };

        return VOsaka::spawn($fn());
    }

    /**
     * Get local address
     */
    public function localAddr(): string
    {
        return "{$this->host}:{$this->port}";
    }

    /**
     * Close the listener
     */
    public function close(): void
    {
        if ($this->socket) {
            @fclose($this->socket);
            $this->socket = null;
        }
        $this->isListening = false;
    }

    public function isClosed(): bool
    {
        return !$this->isListening;
    }
}