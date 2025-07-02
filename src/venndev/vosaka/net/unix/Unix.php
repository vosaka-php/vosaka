<?php

declare(strict_types=1);

namespace venndev\vosaka\net\unix;

use Generator;
use InvalidArgumentException;
use venndev\vosaka\time\Sleep;
use venndev\vosaka\core\Result;
use venndev\vosaka\core\Future;
use venndev\vosaka\VOsaka;

/**
 * Unix class for creating asynchronous Unix domain socket connections.
 *
 * This class provides static methods for establishing Unix domain socket connections
 * in an asynchronous manner that works with the VOsaka event loop. It supports both
 * stream and datagram Unix domain sockets with configurable options.
 *
 * All connection operations are non-blocking and return Result objects that
 * can be awaited using VOsaka's async runtime. The class handles connection
 * establishment, timeout management, and proper socket configuration for
 * async operations.
 */
final class Unix
{
    /**
     * Connect to a Unix domain socket asynchronously.
     *
     * Establishes a connection to the specified Unix domain socket path. The connection
     * is created asynchronously and the socket is configured for non-blocking
     * operation to work with the VOsaka event loop.
     *
     * The path should be a valid Unix domain socket path on the filesystem.
     * The path length is limited to 108 characters (typical Unix socket path limit).
     *
     * Available options:
     * - 'timeout' (int): Connection timeout in seconds (default: 30)
     * - 'reuseaddr' (bool): Whether to reuse the address (default: true)
     *
     * @param string $path Path to the Unix domain socket
     * @param array $options Additional connection options
     * @return Result<UnixStream> A Result containing the UnixStream on success
     * @throws InvalidArgumentException If the path is invalid or connection fails
     */
    public static function connect(string $path, array $options = []): Result
    {
        $fn = function () use ($path, $options): Generator {
            self::validatePath($path);

            $timeout = $options["timeout"] ?? 30;
            $context = self::createContext($options);

            $socket = @stream_socket_client(
                "unix://{$path}",
                $errno,
                $errstr,
                $timeout,
                STREAM_CLIENT_CONNECT,
                $context
            );

            if (! $socket) {
                throw new InvalidArgumentException(
                    "Failed to connect to Unix socket {$path}: $errstr"
                );
            }

            stream_set_blocking($socket, false);
            VOsaka::getLoop()->getGracefulShutdown()->addSocket($socket);

            yield Sleep::new(0.001); // Allow for async operation

            return new UnixStream($socket, $path);
        };

        return Future::new($fn());
    }

    /**
     * Create a Unix datagram socket.
     *
     * Creates a new Unix datagram socket that can be used for connectionless
     * communication over Unix domain sockets. The socket must be bound to a
     * path before it can be used for sending or receiving data.
     *
     * Available options:
     * - 'reuseaddr' (bool): Whether to reuse the address (default: true)
     *
     * @param array $options Socket configuration options
     * @return UnixDatagram A new UnixDatagram instance
     */
    public static function datagram(array $options = []): UnixDatagram
    {
        return UnixDatagram::new();
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
    }

    /**
     * Create stream context with options.
     *
     * @param array $options Context options
     * @return resource Stream context
     */
    private static function createContext(array $options = [])
    {
        $context = stream_context_create();

        if ($options["reuseaddr"] ?? true) {
            stream_context_set_option($context, "socket", "so_reuseaddr", 1);
        }

        return $context;
    }
}
