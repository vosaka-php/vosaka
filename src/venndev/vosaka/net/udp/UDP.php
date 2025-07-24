<?php

declare(strict_types=1);

namespace venndev\vosaka\net\udp;

use venndev\vosaka\core\Result;
use venndev\vosaka\core\Future;
use venndev\vosaka\net\contracts\AddressInterface;
use venndev\vosaka\net\exceptions\NetworkException;
use venndev\vosaka\net\tcp\TCPAddress;
use venndev\vosaka\net\SocketFactory;
use Generator;

/**
 * UDP factory
 */
class UDP
{
    /**
     * Create UDP socket bound to address
     * @param string|AddressInterface $address
     * @param array $options
     * @return Result<UDPSocket>
     */
    public static function bind(string|AddressInterface $address, array $options = []): Result
    {
        return Future::new(self::doBind($address, $options));
    }

    /**
     * Bind UDP socket to a specific address
     *
     * @param string|AddressInterface $address
     * @param array $options
     * @return Generator<UDPSocket>
     * @throws NetworkException
     */
    private static function doBind(string|AddressInterface $address, array $options): Generator
    {
        if (is_string($address)) {
            $address = TCPAddress::parse($address);
        }

        $context = SocketFactory::createContext($options);

        $socket = @stream_socket_server(
            "udp://{$address->getHost()}:{$address->getPort()}",
            $errno,
            $errstr,
            STREAM_SERVER_BIND,
            $context
        );

        if (!$socket) {
            throw new NetworkException("Failed to bind UDP socket: {$errstr}");
        }

        // Apply socket options
        SocketFactory::applyOptions($socket, $options);

        yield;

        return new UDPSocket($socket, $options);
    }

    /**
     * Create unbound UDP socket
     * @param string $family 'v4' or 'v6'
     * @param array $options Socket options
     * @return Result<UDPSocket>
     */
    public static function socket(string $family = 'v4', array $options = []): Result
    {
        return Future::new(self::doSocket($family, $options));
    }

    /**
     * Create an unbound UDP socket
     *
     * @param string $family 'v4' or 'v6'
     * @param array $options Socket options
     * @return Generator<UDPSocket>
     * @throws NetworkException
     */
    private static function doSocket(string $family, array $options): Generator
    {
        $host = $family === 'v6' ? '::' : '0.0.0.0';
        $context = SocketFactory::createContext($options);

        $socket = @stream_socket_server(
            "udp://{$host}:0",
            $errno,
            $errstr,
            STREAM_SERVER_BIND,
            $context
        );

        if (!$socket) {
            throw new NetworkException("Failed to create UDP socket: {$errstr}");
        }

        // Apply socket options
        SocketFactory::applyOptions($socket, $options);

        yield;

        return new UDPSocket($socket, $options);
    }
}
