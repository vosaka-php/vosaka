<?php

declare(strict_types=1);

namespace venndev\vosaka\net\options;

/**
 * Fluent options builder
 */
class OptionsBuilder
{
    /**
     * Create TCP client options
     */
    public static function tcpClient(): SocketOptions
    {
        return SocketOptions::create()
            ->setNoDelay(true)
            ->setKeepAlive(true);
    }

    /**
     * Create TCP server options
     */
    public static function tcpServer(): ServerOptions
    {
        return (new ServerOptions())
            ->setReuseAddr(true)
            ->setReusePort(PHP_OS_FAMILY !== 'Windows');
    }

    /**
     * Create SSL/TLS client options
     */
    public static function tlsClient(): SocketOptions
    {
        return SocketOptions::create()
            ->enableSsl(true)
            ->setVerifyPeer(true);
    }

    /**
     * Create SSL/TLS server options
     */
    public static function tlsServer(string $cert, string $key): ServerOptions
    {
        return (new ServerOptions())
            ->enableSsl(true)
            ->setSslCertificate($cert)
            ->setSslKey($key)
            ->setReuseAddr(true);
    }

    /**
     * Create high-performance options
     */
    public static function highPerformance(): SocketOptions
    {
        return SocketOptions::create()
            ->setNoDelay(true)
            ->setSendBufferSize(262144)    // 256KB
            ->setReceiveBufferSize(262144) // 256KB
            ->setReusePort(PHP_OS_FAMILY !== 'Windows');
    }

    /**
     * Create low-latency options
     */
    public static function lowLatency(): SocketOptions
    {
        return SocketOptions::create()
            ->setNoDelay(true)
            ->setSendBufferSize(8192)     // 8KB
            ->setReceiveBufferSize(8192); // 8KB
    }
}
