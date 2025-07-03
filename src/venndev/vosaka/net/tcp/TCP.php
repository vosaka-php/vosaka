<?php

declare(strict_types=1);

namespace venndev\vosaka\net\tcp;

use Generator;
use InvalidArgumentException;
use venndev\vosaka\core\Result;
use venndev\vosaka\core\Future;
use venndev\vosaka\core\interfaces\NewFn;
use venndev\vosaka\net\SocketBase;

final class TCP extends SocketBase
{
    public static function connect(string $addr, array $options = []): Result
    {
        $fn = function () use ($addr, $options): Generator {
            yield;
            [$host, $port] = self::parseAddr($addr);
            $protocol = $options["ssl"] ?? false ? "ssl" : "tcp";
            $context = self::createContext(
                array_merge($options, [
                    "timeout" => $options["timeout"] ?? 30,
                ])
            );

            $socket = @stream_socket_client(
                "{$protocol}://{$host}:{$port}",
                $errno,
                $errstr,
                $options["timeout"] ?? 30,
                STREAM_CLIENT_CONNECT,
                $context
            );

            if (!$socket) {
                throw new InvalidArgumentException(
                    "Failed to connect to {$addr}: $errstr"
                );
            }

            self::addToEventLoop($socket);
            self::applySocketOptions($socket, $options);

            return new TCPStream($socket, $addr);
        };

        return Future::new($fn());
    }
}
