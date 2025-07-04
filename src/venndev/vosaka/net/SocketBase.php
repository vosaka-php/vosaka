<?php

declare(strict_types=1);

namespace venndev\vosaka\net;

use InvalidArgumentException;
use Throwable;
use venndev\vosaka\VOsaka;
use venndev\vosaka\net\option\PlatformOptionsFactory;
use venndev\vosaka\net\option\SocketOptions;
use venndev\vosaka\utils\PlatformDetector;

abstract class SocketBase
{
    protected mixed $socket = null;
    protected array $options = [];

    /**
     * @param array $options
     * @return resource
     */
    protected static function createContext(array $options = [])
    {
        $context = stream_context_create();

        if ($options["reuseaddr"] ?? true) {
            stream_context_set_option($context, "socket", "so_reuseaddr", 1);
        }

        if ($options["reuseport"] ?? false) {
            stream_context_set_option($context, "socket", "so_reuseport", 1);
        }

        if ($options["nodelay"] ?? false) {
            stream_context_set_option($context, "socket", "tcp_nodelay", 1);
        }

        if ($options["keepalive"] ?? false) {
            stream_context_set_option($context, "socket", "so_keepalive", 1);
        }

        if ($options["sndbuf"] ?? 0 > 0) {
            stream_context_set_option(
                $context,
                "socket",
                "so_sndbuf",
                $options["sndbuf"]
            );
        }

        if ($options["rcvbuf"] ?? 0 > 0) {
            stream_context_set_option(
                $context,
                "socket",
                "so_rcvbuf",
                $options["rcvbuf"]
            );
        }

        if ($options["backlog"] ?? 0 > SOMAXCONN) {
            stream_context_set_option(
                $context,
                "socket",
                "backlog",
                $options["backlog"]
            );
        }

        if ($options["ssl"] ?? false) {
            stream_context_set_option(
                $context,
                "ssl",
                "verify_peer",
                $options["verify_tls"] ?? false
            );
            stream_context_set_option(
                $context,
                "ssl",
                "verify_peer_name",
                $options["verify_tls"] ?? false
            );
            stream_context_set_option(
                $context,
                "ssl",
                "allow_self_signed",
                true
            );

            if ($options["ssl_cert"] ?? null) {
                stream_context_set_option(
                    $context,
                    "ssl",
                    "local_cert",
                    $options["ssl_cert"]
                );
            }
            if ($options["ssl_key"] ?? null) {
                stream_context_set_option(
                    $context,
                    "ssl",
                    "local_pk",
                    $options["ssl_key"]
                );
            }
        }

        if (
            $options["defer_accept"] ?? false && !PlatformDetector::isWindows()
        ) {
            stream_context_set_option(
                $context,
                "socket",
                "tcp_defer_accept",
                1
            );
        }

        return $context;
    }

    protected static function applySocketOptions(
        mixed $socket,
        array $options
    ): void {
        if (
            !$socket ||
            !is_resource($socket) ||
            get_resource_type($socket) !== "stream" ||
            !function_exists("socket_import_stream")
        ) {
            return;
        }

        $meta = stream_get_meta_data($socket);
        $streamType = $meta["stream_type"] ?? "";

        // No support for SSL
        if (str_contains($streamType, "ssl")) {
            return;
        }

        $sock = @socket_import_stream($socket);
        if ($sock === false) {
            return;
        }

        try {
            if ($options["reuseaddr"] ?? true) {
                socket_set_option($sock, SOL_SOCKET, SO_REUSEADDR, 1);
            }

            if ($options["reuseport"] ?? false) {
                if (!defined("SO_REUSEPORT")) {
                    if (
                        PHP_OS_FAMILY === "Windows" &&
                        defined("SO_REUSEADDR")
                    ) {
                        socket_set_option($sock, SOL_SOCKET, SO_REUSEADDR, 1);
                    } else {
                        define("SO_REUSEPORT", 15);
                        socket_set_option($sock, SOL_SOCKET, SO_REUSEPORT, 1);
                    }
                } else {
                    socket_set_option($sock, SOL_SOCKET, SO_REUSEPORT, 1);
                }
            }

            if ($options["keepalive"] ?? false) {
                socket_set_option($sock, SOL_SOCKET, SO_KEEPALIVE, 1);
            }

            if ($options["nodelay"] ?? false) {
                if (!defined("IPPROTO_TCP")) {
                    define("IPPROTO_TCP", 6);
                }
                if (!defined("TCP_NODELAY")) {
                    define("TCP_NODELAY", 1);
                }
                socket_set_option($sock, IPPROTO_TCP, TCP_NODELAY, 1);
            }

            if (($options["sndbuf"] ?? 0) > 0) {
                socket_set_option(
                    $sock,
                    SOL_SOCKET,
                    SO_SNDBUF,
                    $options["sndbuf"]
                );
            }

            if (($options["rcvbuf"] ?? 0) > 0) {
                socket_set_option(
                    $sock,
                    SOL_SOCKET,
                    SO_RCVBUF,
                    $options["rcvbuf"]
                );
            }

            if (($options["linger"] ?? null) === false) {
                socket_set_option($sock, SOL_SOCKET, SO_LINGER, [
                    "l_onoff" => 1,
                    "l_linger" => 0,
                ]);
            }

            if ($options["fast_open"] ?? false && PHP_OS_FAMILY !== "Windows") {
                if (!defined("TCP_FASTOPEN")) {
                    define("TCP_FASTOPEN", 23);
                }

                if (defined("SO_REUSEPORT")) {
                    @socket_set_option($sock, SOL_SOCKET, SO_REUSEPORT, 1);
                }
            }
        } catch (Throwable $e) {
            error_log(
                "Warning: Could not set socket options: {$e->getMessage()}"
            );
        }
    }

    protected static function validatePath(string $path): void
    {
        if (empty($path)) {
            throw new InvalidArgumentException(
                "Unix socket path cannot be empty"
            );
        }

        if (strlen($path) > 108) {
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

    protected static function parseAddr(string $addr): array
    {
        if (strpos($addr, ":") === false) {
            throw new InvalidArgumentException(
                "Invalid address format. Expected 'host:port'"
            );
        }

        $parts = explode(":", $addr);
        $port = (int) array_pop($parts);
        $host = implode(":", $parts);

        if ($port < 1 || $port > 65535) {
            throw new InvalidArgumentException(
                "Port must be between 1 and 65535: {$port}"
            );
        }

        return [$host, $port];
    }

    protected static function addToEventLoop(mixed $socket): void
    {
        if ($socket) {
            stream_set_blocking($socket, false);
            VOsaka::getLoop()->getGracefulShutdown()->addSocket($socket);
        }
    }

    protected static function removeFromEventLoop(mixed $socket): void
    {
        if ($socket) {
            VOsaka::getLoop()->getGracefulShutdown()->removeSocket($socket);
        }
    }

    /**
     * Normalizes the provided socket options.
     * If an instance of SocketOptions is provided, it converts it to an array.
     * If an array is provided, it merges it with the default options.
     * If no options are provided, it returns the default socket options.
     * @param array|SocketOptions|null $options
     * @return array
     */
    protected static function normalizeOptions(
        array|SocketOptions|null $options = null
    ): array {
        if ($options instanceof SocketOptions) {
            return $options->toArray();
        } elseif (is_array($options) && !empty($options)) {
            return array_merge(
                PlatformOptionsFactory::createSocketOptions()->toArray(),
                $options
            );
        } else {
            return PlatformOptionsFactory::createSocketOptions()->toArray();
        }
    }
}
