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
                "backlog" => SOMAXCONN,
            ],
            $options
        );
    }

    /**
     * Create a new Unix domain socket listener
     * @param string $path Path to the Unix domain socket
     * @param array $options Additional options like 'reuseaddr', 'backlog'
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

        return VOsaka::spawn($fn());
    }

    /**
     * Bind the socket to the specified path
     * @return Result<void>
     */
    private function bindSocket(): Result
    {
        $fn = function (): Generator {
            try {
                // Remove existing socket file if it exists
                if (file_exists($this->path)) {
                    unlink($this->path);
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
                        "Failed to bind to {$this->path}: $errstr"
                    );
                }

                yield stream_set_blocking($this->socket, false);
                VOsaka::getLoop()
                    ->getGracefulShutdown()
                    ->addSocket($this->socket);
                $this->isListening = true;
            } catch (Throwable $e) {
                throw new InvalidArgumentException(
                    "Bind failed: " . $e->getMessage()
                );
            }
        };

        return VOsaka::spawn($fn());
    }

    private function createContext()
    {
        $context = stream_context_create();

        if ($this->options["reuseaddr"]) {
            stream_context_set_option($context, "socket", "so_reuseaddr", 1);
        }

        return $context;
    }

    /**
     * Accept incoming connections
     * @return Result<UnixStream>
     */
    public function accept(): Result
    {
        $fn = function (): Generator {
            if (!$this->isListening) {
                throw new InvalidArgumentException("Listener is not bound");
            }

            while (true) {
                $clientSocket = @stream_socket_accept(
                    $this->socket,
                    0,
                    $peerName
                );

                if ($clientSocket) {
                    stream_set_blocking($clientSocket, false);
                    VOsaka::getLoop()
                        ->getGracefulShutdown()
                        ->addSocket($clientSocket);
                    return new UnixStream(
                        $clientSocket,
                        $peerName ?: "unix:unknown"
                    );
                }

                yield;
            }
        };

        return VOsaka::spawn($fn());
    }

    /**
     * Get local path
     */
    public function localPath(): string
    {
        return $this->path;
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
