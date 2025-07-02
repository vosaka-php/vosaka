<?php

declare(strict_types=1);

namespace venndev\vosaka\net\tcp;

use Generator;
use InvalidArgumentException;
use venndev\vosaka\core\Result;
use venndev\vosaka\core\Future;

/**
 * TCP class for creating asynchronous TCP connections.
 *
 * This class provides static methods for establishing TCP connections in an
 * asynchronous manner that works with the VOsaka event loop. It supports both
 * regular TCP connections and SSL/TLS encrypted connections with configurable
 * options.
 *
 * All connection operations are non-blocking and return Result objects that
 * can be awaited using VOsaka's async runtime. The class handles connection
 * establishment, timeout management, and proper socket configuration for
 * async operations.
 */
final class TCP
{
    /**
     * Connect to a remote TCP address asynchronously.
     *
     * Establishes a TCP connection to the specified remote address. The connection
     * is created asynchronously and the socket is configured for non-blocking
     * operation to work with the VOsaka event loop. Supports both plain TCP and
     * SSL/TLS encrypted connections.
     *
     * The address should be in the format 'host:port' where host can be an IP
     * address or hostname, and port is the numeric port number.
     *
     * Available options:
     * - 'ssl' (bool): Whether to use SSL/TLS encryption (default: false)
     * - 'timeout' (int): Connection timeout in seconds (default: 30)
     *
     * @param string $addr Address in the format 'host:port'
     * @param array $options Additional connection options
     * @return Result<TCPStream> A Result containing the TCPStream on success
     * @throws InvalidArgumentException If the address format is invalid or connection fails
     */
    public static function connect(string $addr, array $options = []): Result
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

            $protocol = $options["ssl"] ?? false ? "ssl" : "tcp";
            $context = stream_context_create();

            $socket = @stream_socket_client(
                "{$protocol}://{$host}:{$port}",
                $errno,
                $errstr,
                $options["timeout"] ?? 30,
                STREAM_CLIENT_CONNECT,
                $context
            );

            if (! $socket) {
                throw new InvalidArgumentException(
                    "Failed to connect to {$addr}: $errstr"
                );
            }

            yield stream_set_blocking($socket, false);
            return new TCPStream($socket, $addr);
        };

        return Future::new($fn());
    }
}
