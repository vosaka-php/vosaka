<?php

declare(strict_types=1);

namespace venndev\vosaka\net\tcp;

use Generator;
use InvalidArgumentException;
use venndev\vosaka\VOsaka;

final class TCPSock
{
    private mixed $socket = null;
    private bool $bound = false;
    private string $addr = '';
    private int $port = 0;
    private array $options = [];

    private function __construct(private readonly string $family = 'v4')
    {
        $this->options = [
            'keepalive' => true,
            'nodelay' => true,
            'reuseaddr' => true,
            'ssl' => false,
            'ssl_cert' => null,
            'ssl_key' => null,
            'verify_tls' => true,
            'backlog' => SOMAXCONN
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

    public function bind(string $addr): Generator
    {
        [$host, $port] = $this->parseAddr($addr);
        $this->addr = $host;
        $this->port = $port;

        $bindTask = function (): Generator {
            $context = $this->createContext();

            $this->socket = yield @stream_socket_server(
                "tcp://{$this->addr}:{$this->port}",
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
    }

    public function listen(int $backlog = SOMAXCONN): Generator
    {
        if (!$this->bound) {
            throw new InvalidArgumentException("Socket must be bound before listening");
        }

        $listenTask = function () use ($backlog): Generator {
            $protocol = $this->options['ssl'] ? 'ssl' : 'tcp';
            $context = $this->createContext();

            if (!stream_socket_shutdown($this->socket, STREAM_SHUT_RDWR)) {
                fclose($this->socket);
                VOsaka::getLoop()->getGracefulShutdown()->cleanup();

                $this->socket = yield @stream_socket_server(
                    "{$protocol}://{$this->addr}:{$this->port}",
                    $errno,
                    $errstr,
                    STREAM_SERVER_BIND | STREAM_SERVER_LISTEN,
                    $context
                );
                VOsaka::getLoop()->getGracefulShutdown()->addSocket($this->socket);

                if (!$this->socket) {
                    throw new InvalidArgumentException("Listen failed: $errstr ($errno)");
                }

                stream_set_blocking($this->socket, false);
            }
        };

        yield from VOsaka::spawn($listenTask())->unwrap();

        return new TCPListener($this->addr, $this->port, [
            'reuseaddr' => $this->options['reuseaddr'],
            'backlog' => $backlog,
            'ssl' => $this->options['ssl'],
            'ssl_cert' => $this->options['ssl_cert'],
            'ssl_key' => $this->options['ssl_key']
        ]);
    }

    public function connect(string $addr): Generator
    {
        [$host, $port] = $this->parseAddr($addr);

        $connectTask = function () use ($host, $port): Generator {
            $protocol = $this->options['ssl'] ? 'ssl' : 'tcp';
            $context = $this->createContext();

            $this->socket = yield @stream_socket_client(
                "{$protocol}://{$host}:{$port}",
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

        return new TCPStream($this->socket, $host . ':' . $port);
    }

    public function setReuseAddr(bool $reuseAddr): self
    {
        $this->options['reuseaddr'] = $reuseAddr;

        return $this;
    }

    public function setReusePort(bool $reusePort): self
    {
        $this->options['reuseport'] = $reusePort;

        return $this;
    }

    public function setKeepAlive(bool $keepAlive): self
    {
        $this->options['keepalive'] = $keepAlive;

        return $this;
    }

    public function setNoDelay(bool $noDelay): self
    {
        $this->options['nodelay'] = $noDelay;

        return $this;
    }

    public function setSsl(bool $ssl, ?string $sslCert = null, ?string $sslKey = null): self
    {
        $this->options['ssl'] = $ssl;
        $this->options['ssl_cert'] = $sslCert;
        $this->options['ssl_key'] = $sslKey;

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

        if ($this->options['ssl']) {
            stream_context_set_option($context, 'ssl', 'verify_peer', $this->options['verify_tls']);
            stream_context_set_option($context, 'ssl', 'verify_peer_name', $this->options['verify_tls']);
            if ($this->options['ssl_cert']) {
                stream_context_set_option($context, 'ssl', 'local_cert', $this->options['ssl_cert']);
            }
            if ($this->options['ssl_key']) {
                stream_context_set_option($context, 'ssl', 'local_pk', $this->options['ssl_key']);
            }
            stream_context_set_option($context, 'ssl', 'allow_self_signed', true);
        }

        if ($this->options['reuseaddr']) {
            stream_context_set_option($context, 'socket', 'so_reuseaddr', 1);
        }

        if ($this->options['reuseport'] ?? false) {
            stream_context_set_option($context, 'socket', 'so_reuseport', 1);
        }

        return $context;
    }

    private function configureSocket(): void
    {
        if (!$this->socket) {
            return;
        }

        stream_set_blocking($this->socket, false);

        if ($this->options['keepalive']) {
            socket_set_option($this->socket, SOL_SOCKET, SO_KEEPALIVE, 1);
        }

        if ($this->options['nodelay']) {
            socket_set_option($this->socket, SOL_TCP, TCP_NODELAY, 1);
        }

        if ($this->options['reuseaddr']) {
            socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1);
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
}