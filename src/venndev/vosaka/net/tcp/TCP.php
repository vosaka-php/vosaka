<?php

declare(strict_types=1);

namespace venndev\vosaka\net\tcp;

use Generator;
use venndev\vosaka\core\Future;
use venndev\vosaka\core\Result;
use venndev\vosaka\net\contracts\AddressInterface;
use venndev\vosaka\net\exceptions\BindException;
use venndev\vosaka\net\exceptions\ConnectionException;
use venndev\vosaka\net\SocketFactory;
use venndev\vosaka\net\tcp\TCPConnection;
use venndev\vosaka\net\tcp\TCPServer;

/**
 * TCP client/server factory
 */
class TCP
{
    /**
     * Connect to TCP server
     *
     * @param string|AddressInterface $address
     * @param array $options Connection options
     * @return Result<TCPConnection>
     */
    public static function connect(string|AddressInterface $address, array $options = []): Result
    {
        return Future::new(self::doConnect($address, $options));
    }

    /**
     * Internal method to handle TCP connection
     *
     * @param string|AddressInterface $address
     * @param array $options Connection options
     * @return Generator<TCPConnection>
     * @throws ConnectionException
     */
    private static function doConnect(string|AddressInterface $address, array $options): Generator
    {
        if (is_string($address)) {
            $address = TCPAddress::parse($address);
        }

        $timeout = $options['timeout'] ?? 30.0;
        $context = SocketFactory::createContext($options);

        $socket = @stream_socket_client(
            "tcp://{$address->getHost()}:{$address->getPort()}",
            $errno,
            $errstr,
            $timeout,
            STREAM_CLIENT_CONNECT | STREAM_CLIENT_ASYNC_CONNECT,
            $context
        );

        if (!$socket) {
            throw new ConnectionException("Failed to connect to {$address->toString()}: {$errstr}");
        }

        // Apply socket options
        SocketFactory::applyOptions($socket, $options);

        // Get local address
        $localName = stream_socket_get_name($socket, false);
        $localAddress = $localName ? TCPAddress::parse($localName) : null;

        yield;

        return new TCPConnection($socket, $localAddress, $address);
    }

    /**
     * Create TCP server
     *
     * @param string|AddressInterface $address
     * @param array $options Server options
     * @return Result<TCPServer>
     */
    public static function listen(string|AddressInterface $address, array $options = []): Result
    {
        return Future::new(self::doListen($address, $options));
    }

    /**
     * Internal method to handle TCP server creation
     *
     * @param string|AddressInterface $address
     * @param array $options Server options
     * @return Generator<TCPServer>
     * @throws BindException
     */
    private static function doListen(string|AddressInterface $address, array $options): Generator
    {
        if (is_string($address)) {
            $address = TCPAddress::parse($address);
        }

        $backlog = $options['backlog'] ?? SOMAXCONN;
        $context = SocketFactory::createContext($options);

        $socket = @stream_socket_server(
            "tcp://{$address->getHost()}:{$address->getPort()}",
            $errno,
            $errstr,
            STREAM_SERVER_BIND | STREAM_SERVER_LISTEN,
            $context
        );

        if (!$socket) {
            throw new BindException("Failed to bind to {$address->toString()}: {$errstr}");
        }

        // Apply socket options
        SocketFactory::applyOptions($socket, $options);

        yield;

        return new TCPServer($socket, $address, $options);
    }
}
