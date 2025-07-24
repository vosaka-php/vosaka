<?php

declare(strict_types=1);

namespace venndev\vosaka\net\unix;

use venndev\vosaka\core\Result;
use venndev\vosaka\core\Future;
use venndev\vosaka\net\exceptions\ConnectionException;
use venndev\vosaka\net\exceptions\BindException;
use venndev\vosaka\net\SocketFactory;
use venndev\vosaka\net\unix\UnixAddress;
use Generator;

/**
 * Unix socket factory
 */
class UnixSocket
{
    /**
     * Connect to Unix socket
     *
     * @param string|UnixAddress $address
     * @param array $options
     * @return Result<UnixConnection>
     */
    public static function connect(string|UnixAddress $address, array $options = []): Result
    {
        return Future::new(self::doConnect($address, $options));
    }

    /**
     * Connect to a Unix socket
     *
     * @param string|UnixAddress $address
     * @param array $options
     * @return Generator<UnixConnection>
     * @throws ConnectionException
     */
    private static function doConnect(string|UnixAddress $address, array $options): Generator
    {
        if (is_string($address)) {
            $address = UnixAddress::parse($address);
        }

        UnixAddress::validate($address->getPath());

        $timeout = $options['timeout'] ?? 30.0;
        $context = SocketFactory::createContext($options);

        $socket = @stream_socket_client(
            "unix://{$address->toString()}",
            $errno,
            $errstr,
            $timeout,
            STREAM_CLIENT_CONNECT | STREAM_CLIENT_ASYNC_CONNECT,
            $context
        );

        if (!$socket) {
            throw new ConnectionException("Failed to connect to Unix socket: {$errstr}");
        }

        yield;

        $localAddress = new UnixAddress("client:" . uniqid(), true);
        return new UnixConnection($socket, $localAddress, $address);
    }

    /**
     * Create Unix socket server
     *
     * @param string|UnixAddress $address
     * @param array $options
     * @return Result<UnixServer>
     */
    public static function listen(string|UnixAddress $address, array $options = []): Result
    {
        return Future::new(self::doListen($address, $options));
    }

    /**
     * Create a Unix socket server
     *
     * @param string|UnixAddress $address
     * @param array $options
     * @return Generator<UnixServer>
     * @throws BindException
     */
    private static function doListen(string|UnixAddress $address, array $options): Generator
    {
        if (is_string($address)) {
            $address = UnixAddress::parse($address);
        }

        UnixAddress::validate($address->getPath());

        // Remove existing socket file
        if (!$address->isAbstract() && file_exists($address->getPath())) {
            @unlink($address->getPath());
        }

        $context = SocketFactory::createContext($options);

        $socket = @stream_socket_server(
            "unix://{$address->toString()}",
            $errno,
            $errstr,
            STREAM_SERVER_BIND | STREAM_SERVER_LISTEN,
            $context
        );

        if (!$socket) {
            throw new BindException("Failed to bind Unix socket: {$errstr}");
        }

        // Set permissions if specified
        if (!$address->isAbstract() && isset($options['mode'])) {
            @chmod($address->getPath(), $options['mode']);
        }

        yield;

        return new UnixServer($socket, $address, $options);
    }

    /**
     * Create Unix datagram socket (SOCK_DGRAM)
     *
     * @param string|UnixAddress $address
     * @param array $options
     * @return Result<\venndev\vosaka\net\udp\UDPSocket>
     */
    public static function datagram(string|UnixAddress $address, array $options = []): Result
    {
        return Future::new(self::doDatagram($address, $options));
    }

    /**
     * Create a Unix datagram socket
     *
     * @param string|UnixAddress $address
     * @param array $options
     * @return Generator<\venndev\vosaka\net\udp\UDPSocket>
     * @throws BindException
     */
    private static function doDatagram(string|UnixAddress $address, array $options): Generator
    {
        if (is_string($address)) {
            $address = UnixAddress::parse($address);
        }

        UnixAddress::validate($address->getPath());

        // Remove existing socket file
        if (!$address->isAbstract() && file_exists($address->getPath())) {
            @unlink($address->getPath());
        }

        $context = SocketFactory::createContext($options);

        $socket = @stream_socket_server(
            "udg://{$address->toString()}",
            $errno,
            $errstr,
            STREAM_SERVER_BIND,
            $context
        );

        if (!$socket) {
            throw new BindException("Failed to create Unix datagram socket: {$errstr}");
        }

        yield;

        // Return a UDP-like socket for Unix datagrams
        return new \venndev\vosaka\net\udp\UDPSocket($socket, $options);
    }
}
