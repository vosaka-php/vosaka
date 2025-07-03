<?php

declare(strict_types=1);

namespace venndev\vosaka\net\unix;

use Generator;
use InvalidArgumentException;
use venndev\vosaka\core\Result;
use venndev\vosaka\core\Future;
use venndev\vosaka\net\SocketBase;
use venndev\vosaka\VOsaka;

final class Unix extends SocketBase
{
    public function connect(string $path, array $options = []): Result
    {
        $fn = function () use ($path, $options): Generator {
            yield;
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

            if (!$socket) {
                throw new InvalidArgumentException(
                    "Failed to connect to Unix socket {$path}: $errstr"
                );
            }

            self::addToEventLoop($socket);
            self::applySocketOptions($socket, $options);

            return new UnixStream($socket, $path);
        };

        return Future::new($fn());
    }
}
