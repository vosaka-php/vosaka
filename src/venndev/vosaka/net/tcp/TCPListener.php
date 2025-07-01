<?php

declare(strict_types=1);

namespace venndev\vosaka\net\tcp;

use Generator;
use InvalidArgumentException;
use Throwable;
use venndev\vosaka\core\Result;
use venndev\vosaka\utils\CallableUtil;
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
        $this->options = array_merge(
            [
                "reuseaddr" => true,
                "reuseport" => false,
                "backlog" => min(65535, SOMAXCONN * 4),
                "ssl" => false,
                "ssl_cert" => null,
                "ssl_key" => null,
                "nodelay" => true,
                "keepalive" => true,
                "defer_accept" => true,
                "fast_open" => true,
                "linger" => false,
                "sndbuf" => 65536,
                "rcvbuf" => 65536,
                "max_connections" => 10000,
            ],
            $options
        );
    }

    /**
     * Create a new TCP listener
     * @param string $addr Address in 'host:port' format
     * @param array $options Additional options like 'ssl', 'reuseport', etc.
     * @return Result<TCPListener>
     */
    public static function bind(string $addr, array $options = []): Result
    {
        $fn = function () use ($addr, $options): Generator {
            $parts = explode(":", $addr);
            if (count($parts) !== 2) {
                throw new InvalidArgumentException(
                    "Invalid address format. Use 'host:port'"
                );
            }

            $host = $parts[0];
            $port = (int) $parts[1];

            if ($port < 1 || $port > 65535) {
                throw new InvalidArgumentException(
                    "Port must be between 1 and 65535"
                );
            }

            $listener = new self($host, $port, $options);
            yield from $listener->bindSocket()->unwrap();

            return $listener;
        };

        return Result::c($fn());
    }

    /**
     * Bind the socket to the specified address and port
     * @return Result<void>
     */
    private function bindSocket(): Result
    {
        $fn = function (): Generator {
            try {
                $protocol = $this->options["ssl"] ? "ssl" : "tcp";
                $context = $this->createContext();

                $this->socket = @stream_socket_server(
                    "{$protocol}://{$this->host}:{$this->port}",
                    $errno,
                    $errstr,
                    STREAM_SERVER_BIND | STREAM_SERVER_LISTEN,
                    $context
                );

                if (!$this->socket) {
                    throw new InvalidArgumentException(
                        "Failed to bind to {$this->host}:{$this->port}: $errstr (errno: $errno)"
                    );
                }

                yield stream_set_blocking($this->socket, false);

                $this->applySocketOptions();

                $this->isListening = true;

                $this->logSocketOptions();
            } catch (Throwable $e) {
                throw new InvalidArgumentException(
                    "Bind failed: " . $e->getMessage()
                );
            }
        };

        return Result::c($fn());
    }

    private function createContext()
    {
        $context = stream_context_create();

        // SSL context options
        if ($this->options["ssl"]) {
            if (!$this->options["ssl_cert"] || !$this->options["ssl_key"]) {
                throw new InvalidArgumentException(
                    "SSL certificate and key required for SSL"
                );
            }

            stream_context_set_option(
                $context,
                "ssl",
                "local_cert",
                $this->options["ssl_cert"]
            );
            stream_context_set_option(
                $context,
                "ssl",
                "local_pk",
                $this->options["ssl_key"]
            );
            stream_context_set_option($context, "ssl", "verify_peer", false);
            stream_context_set_option(
                $context,
                "ssl",
                "allow_self_signed",
                true
            );
        }

        // Socket context options
        if ($this->options["reuseaddr"]) {
            stream_context_set_option($context, "socket", "so_reuseport", 1);
        }

        if ($this->options["reuseport"]) {
            stream_context_set_option($context, "socket", "so_reuseport", 1);
        }

        if ($this->options["nodelay"]) {
            stream_context_set_option($context, "socket", "tcp_nodelay", 1);
        }

        if ($this->options["keepalive"]) {
            stream_context_set_option($context, "socket", "so_keepalive", 1);
        }

        if ($this->options["defer_accept"] && PHP_OS_FAMILY !== "Windows") {
            stream_context_set_option(
                $context,
                "socket",
                "tcp_defer_accept",
                1
            );
        }

        // Set buffer sizes for better throughput
        if ($this->options["sndbuf"] > 0) {
            stream_context_set_option(
                $context,
                "socket",
                "so_sndbuf",
                $this->options["sndbuf"]
            );
        }

        if ($this->options["rcvbuf"] > 0) {
            stream_context_set_option(
                $context,
                "socket",
                "so_rcvbuf",
                $this->options["rcvbuf"]
            );
        }

        // Backlog size for connection queue
        if ($this->options["backlog"] > SOMAXCONN) {
            stream_context_set_option(
                $context,
                "socket",
                "backlog",
                $this->options["backlog"]
            );
        }

        return $context;
    }

    private function applySocketOptions(): void
    {
        if (!$this->socket) {
            return;
        }

        try {
            if (function_exists("socket_import_stream")) {
                $sock = socket_import_stream($this->socket);
                if ($sock === false) {
                    return;
                }

                // SO_REUSEPORT
                if ($this->options["reuseport"]) {
                    if (!defined("SO_REUSEPORT")) {
                        if (PHP_OS_FAMILY === "Windows") {
                            if (defined("SO_REUSEADDR")) {
                                socket_set_option(
                                    $sock,
                                    SOL_SOCKET,
                                    SO_REUSEADDR,
                                    1
                                );
                            }
                        } else {
                            define("SO_REUSEPORT", 15);
                            socket_set_option(
                                $sock,
                                SOL_SOCKET,
                                SO_REUSEPORT,
                                1
                            );
                        }
                    } else {
                        socket_set_option($sock, SOL_SOCKET, SO_REUSEPORT, 1);
                    }
                }

                // Buffer sizes
                if ($this->options["sndbuf"] > 0) {
                    socket_set_option(
                        $sock,
                        SOL_SOCKET,
                        SO_SNDBUF,
                        $this->options["sndbuf"]
                    );
                }
                if ($this->options["rcvbuf"] > 0) {
                    socket_set_option(
                        $sock,
                        SOL_SOCKET,
                        SO_RCVBUF,
                        $this->options["rcvbuf"]
                    );
                }

                // TCP_FASTOPEN (Linux only)
                if (
                    $this->options["fast_open"] &&
                    PHP_OS_FAMILY !== "Windows"
                ) {
                    if (!defined("TCP_FASTOPEN")) {
                        define("TCP_FASTOPEN", 23); // Linux value
                    }
                    if (!defined("IPPROTO_TCP")) {
                        define("IPPROTO_TCP", 6);
                    }
                    try {
                        socket_set_option($sock, IPPROTO_TCP, TCP_FASTOPEN, 5); // Queue size
                    } catch (Throwable $e) {
                        // TCP_FASTOPEN might not be supported
                    }
                }

                // SO_LINGER control
                if ($this->options["linger"] === false) {
                    $linger = ["l_onoff" => 1, "l_linger" => 0];
                    socket_set_option($sock, SOL_SOCKET, SO_LINGER, $linger);
                }
            }
        } catch (Throwable $e) {
            error_log(
                "Warning: Could not set socket options: " . $e->getMessage()
            );
        }
    }

    private function logSocketOptions(): void
    {
        $enabledOptions = [];

        if ($this->options["reuseaddr"]) {
            $enabledOptions[] = "SO_REUSEADDR";
        }
        if ($this->options["reuseport"]) {
            $enabledOptions[] = "SO_REUSEPORT";
        }
        if ($this->options["nodelay"]) {
            $enabledOptions[] = "TCP_NODELAY";
        }
        if ($this->options["keepalive"]) {
            $enabledOptions[] = "SO_KEEPALIVE";
        }
        if ($this->options["defer_accept"]) {
            $enabledOptions[] = "TCP_DEFER_ACCEPT";
        }
        if ($this->options["fast_open"]) {
            $enabledOptions[] = "TCP_FASTOPEN";
        }
        if ($this->options["ssl"]) {
            $enabledOptions[] = "SSL/TLS";
        }
    }

    /**
     * Accept incoming connections
     * @return Result<TCPStream|null>
     */
    public function accept(): Result
    {
        $fn = function (): Generator {
            yield;
            if (!$this->isListening) {
                throw new InvalidArgumentException("Listener is not bound");
            }

            $clientSocket = @stream_socket_accept($this->socket, 0, $peerName);

            if ($clientSocket) {
                // Set client socket options
                stream_set_blocking($clientSocket, false);

                // Apply TCP_NODELAY to client socket for better performance
                if ($this->options["nodelay"]) {
                    $this->setTcpNodelay($clientSocket);
                }

                return new TCPStream($clientSocket, $peerName);
            }

            return null;
        };

        return Result::c($fn());
    }

    /**
     * Get local address
     */
    public function localAddr(): string
    {
        return "{$this->host}:{$this->port}";
    }

    /**
     * Get socket options info
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Check if SO_REUSEPORT is enabled
     */
    public function isReusePortEnabled(): bool
    {
        return $this->options["reuseport"];
    }

    /**
     * Get socket resource (for advanced usage)
     */
    public function getSocket()
    {
        return $this->socket;
    }

    /**
     * Close the listener
     */
    public function close(): void
    {
        if ($this->socket) {
            VOsaka::getLoop()
                ->getGracefulShutdown()
                ->removeSocket($this->socket);
            @fclose($this->socket);
            $this->socket = null;
        }
        $this->isListening = false;
    }

    /**
     * Set TCP_NODELAY with cross-platform compatibility
     */
    private function setTcpNodelay($socket): void
    {
        try {
            if (function_exists("socket_import_stream")) {
                $sock = socket_import_stream($socket);
                if ($sock !== false) {
                    // Define constants if not available
                    if (!defined("IPPROTO_TCP")) {
                        define("IPPROTO_TCP", 6);
                    }
                    if (!defined("TCP_NODELAY")) {
                        define("TCP_NODELAY", 1);
                    }

                    socket_set_option($sock, IPPROTO_TCP, TCP_NODELAY, 1);
                }
            }
        } catch (Throwable $e) {
            // Fallback: try stream context approach
            try {
                $context = stream_context_get_options(
                    stream_context_get_default()
                );
                $context["socket"]["tcp_nodelay"] = 1;
                stream_context_set_default($context);
            } catch (Throwable $e2) {
                // Ignore, it's just an optimization
                error_log(
                    "Warning: Could not set TCP_NODELAY: " . $e->getMessage()
                );
            }
        }
    }

    public function isClosed(): bool
    {
        return !$this->isListening;
    }
}
