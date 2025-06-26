<?php

declare(strict_types=1);

namespace venndev\vosaka\net\tcp;

use Generator;
use InvalidArgumentException;
use venndev\vosaka\time\Sleep;
use venndev\vosaka\core\Result;
use venndev\vosaka\VOsaka;

final class TCP
{
    /**
     * Connect to remote address
     * @param string $addr Address in the format 'host:port'
     * @param array $options Additional options like 'ssl' (boolean), 'timeout' (int)
     * @return Result<TCPStream>
     */
    public static function connect(string $addr, array $options = []): Result
    {
        $fn = function () use ($addr, $options): Generator {
            $parts = explode(':', $addr);
            if (count($parts) !== 2) {
                throw new InvalidArgumentException("Invalid address format. Use 'host:port'");
            }

            $host = $parts[0];
            $port = (int) $parts[1];

            $protocol = $options['ssl'] ?? false ? 'ssl' : 'tcp';
            $context = stream_context_create();

            $socket = @stream_socket_client(
                "{$protocol}://{$host}:{$port}",
                $errno,
                $errstr,
                $options['timeout'] ?? 30,
                STREAM_CLIENT_CONNECT,
                $context
            );

            if (!$socket) {
                throw new InvalidArgumentException("Failed to connect to {$addr}: $errstr");
            }

            stream_set_blocking($socket, false);

            yield Sleep::c(0.001); // Allow for async operation

            return new TCPStream($socket, $addr);
        };

        return VOsaka::spawn($fn());
    }
}