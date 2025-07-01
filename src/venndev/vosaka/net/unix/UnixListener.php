<?php

declare(strict_types=1);

namespace venndev\vosaka\net\unix;

use Generator;
use InvalidArgumentException;
use Throwable;
use venndev\vosaka\core\Result;
use venndev\vosaka\VOsaka;

final class UnixListener
{
    private mixed $socket = null;
    private bool $isListening = false;
    private array $options = [];

    private function __construct(
        private readonly string $path,
        array $options = []
    ) {
        $this->options = array_merge(
            [
                "reuseaddr" => true,
                "backlog" => min(65535, SOMAXCONN * 4),
                "permissions" => 0660,
                "remove_existing" => true,
                "defer_accept" => true,
                "sndbuf" => 65536,
                "rcvbuf" => 65536,
                "max_connections" => 10000,
                "linger" => false,
            ],
            $options
        );
    }

    /**
     * Create a new Unix domain socket listener
     * @param string $path Path to the Unix domain socket
     * @param array $options Additional options like 'permissions', 'backlog', etc.
     * @return Result<UnixListener>
     */
    public static function bind(string $path, array $options = []): Result
    {
        $fn = function () use ($path, $options): Generator {
            self::validatePath($path);

            $listener = new self($path, $options);
            yield from $listener->bindSocket()->unwrap();

            return $listener;
        };

        return Result::c($fn());
    }

    /**
     * Bind the socket to the specified path
     * @return Result<void>
     */
    private function bindSocket(): Result
    {
        $fn = function (): Generator {
            try {
                // Remove existing socket file if requested
                if (
                    $this->options["remove_existing"] &&
                    file_exists($this->path)
                ) {
                    if (!unlink($this->path)) {
                        throw new InvalidArgumentException(
                            "Failed to remove existing socket file: {$this->path}"
                        );
                    }
                }

                $context = $this->createContext();

                $this->socket = @stream_socket_server(
                    "unix://{$this->path}",
                    $errno,
                    $errstr,
                    STREAM_SERVER_BIND | STREAM_SERVER_LISTEN,
                    $context
                );

                if (!$this->socket) {
                    throw new InvalidArgumentException(
                        "Failed to bind to {$this->path}: $errstr (errno: $errno)"
                    );
                }

                yield stream_set_blocking($this->socket, false);

                // Set socket file permissions
                if (file_exists($this->path)) {
                    chmod($this->path, $this->options["permissions"]);
                }

                $this->applySocketOptions();

                VOsaka::getLoop()
                    ->getGracefulShutdown()
                    ->addSocket($this->socket);

                $this->isListening = true;

                $this->logSocketOptions();
            } catch (Throwable $e) {
                $this->cleanup();
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

        // Socket context options
        if ($this->options["reuseaddr"]) {
            stream_context_set_option($context, "socket", "so_reuseaddr", 1);
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

                // SO_LINGER control
                if ($this->options["linger"] === false) {
                    $linger = ["l_onoff" => 1, "l_linger" => 0];
                    socket_set_option($sock, SOL_SOCKET, SO_LINGER, $linger);
                }

                // Reuse address
                if ($this->options["reuseaddr"]) {
                    socket_set_option($sock, SOL_SOCKET, SO_REUSEADDR, 1);
                }
            }
        } catch (Throwable $e) {
            error_log(
                "Warning: Could not set Unix socket options: " .
                    $e->getMessage()
            );
        }
    }

    private function logSocketOptions(): void
    {
        $enabledOptions = [];

        if ($this->options["reuseaddr"]) {
            $enabledOptions[] = "SO_REUSEADDR";
        }
        if ($this->options["defer_accept"]) {
            $enabledOptions[] = "DEFER_ACCEPT";
        }
        if ($this->options["sndbuf"] > 0) {
            $enabledOptions[] = "SO_SNDBUF({$this->options["sndbuf"]})";
        }
        if ($this->options["rcvbuf"] > 0) {
            $enabledOptions[] = "SO_RCVBUF({$this->options["rcvbuf"]})";
        }

        if (!empty($enabledOptions)) {
            error_log(
                "Unix socket bound to {$this->path} with options: " .
                    implode(", ", $enabledOptions)
            );
        }
    }

    /**
     * Accept incoming connections
     * @return Result<UnixStream|null>
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
                stream_set_blocking($clientSocket, false);

                // Apply socket options to client socket
                $this->applyClientSocketOptions($clientSocket);

                VOsaka::getLoop()
                    ->getGracefulShutdown()
                    ->addSocket($clientSocket);

                return new UnixStream(
                    $clientSocket,
                    $peerName ?: "unix:unknown"
                );
            }

            return null;
        };

        return Result::c($fn());
    }

    private function applyClientSocketOptions($clientSocket): void
    {
        try {
            if (function_exists("socket_import_stream")) {
                $sock = socket_import_stream($clientSocket);
                if ($sock === false) {
                    return;
                }

                // Buffer sizes for client socket
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

                // SO_LINGER control for client
                if ($this->options["linger"] === false) {
                    $linger = ["l_onoff" => 1, "l_linger" => 0];
                    socket_set_option($sock, SOL_SOCKET, SO_LINGER, $linger);
                }
            }
        } catch (Throwable $e) {
            error_log(
                "Warning: Could not set client Unix socket options: " .
                    $e->getMessage()
            );
        }
    }

    /**
     * Get local path
     */
    public function localPath(): string
    {
        return $this->path;
    }

    /**
     * Get socket options info
     */
    public function getOptions(): array
    {
        return $this->options;
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

        $this->cleanup();
    }

    private function cleanup(): void
    {
        // Clean up socket file
        if (file_exists($this->path)) {
            @unlink($this->path);
        }
    }

    public function isClosed(): bool
    {
        return !$this->isListening;
    }

    /**
     * Validate Unix domain socket path.
     *
     * @param string $path Path to validate
     * @throws InvalidArgumentException If path is invalid
     */
    private static function validatePath(string $path): void
    {
        if (empty($path)) {
            throw new InvalidArgumentException(
                "Unix socket path cannot be empty"
            );
        }

        if (strlen($path) > 108) {
            // Typical limit for Unix socket path
            throw new InvalidArgumentException(
                "Unix socket path too long (max 108 characters)"
            );
        }

        $dir = dirname($path);
        if (!is_dir($dir)) {
            throw new InvalidArgumentException(
                "Directory does not exist: {$dir}"
            );
        }

        if (!is_writable($dir)) {
            throw new InvalidArgumentException(
                "Directory is not writable: {$dir}"
            );
        }
    }
}
