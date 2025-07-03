<?php

declare(strict_types=1);

namespace venndev\vosaka\net\tcp;

use Generator;
use InvalidArgumentException;
use venndev\vosaka\core\Result;
use venndev\vosaka\core\Future;
use venndev\vosaka\net\NetworkConstants;
use venndev\vosaka\net\SocketBase;
use venndev\vosaka\net\option\SocketOptions;

final class TCP extends SocketBase
{
    /**
     * Connects to a TCP server.
     *
     * @param string $addr The address to connect to, in the format "host:port".
     * @param array|SocketOptions $options Optional socket options or a SocketOptions instance.
     * @return Result<TCPStream> A Result containing the TCPStream on success.
     * @throws InvalidArgumentException If the address is invalid or connection fails.
     */
    public static function connect(
        string $addr,
        array|SocketOptions $options = []
    ): Result {
        $fn = function () use ($addr, $options): Generator {
            yield;
            [$host, $port] = self::parseAddr($addr);
            $protocol = $options["ssl"] ?? false ? "ssl" : "tcp";
            $opts = self::normalizeOptions($options);
            $context = self::createContext(
                array_merge($opts, [
                    "timeout" =>
                        $opts["timeout"] ?? NetworkConstants::DEFAULT_TIMEOUT,
                ])
            );

            $socket = @stream_socket_client(
                "{$protocol}://{$host}:{$port}",
                $errno,
                $errstr,
                $opts["timeout"] ?? NetworkConstants::DEFAULT_TIMEOUT,
                STREAM_CLIENT_CONNECT,
                $context
            );

            if (!$socket) {
                throw new InvalidArgumentException(
                    "Failed to connect to {$addr}: $errstr"
                );
            }

            self::addToEventLoop($socket);
            self::applySocketOptions($socket, $opts);

            return new TCPStream($socket, $addr);
        };

        return Future::new($fn());
    }
}
