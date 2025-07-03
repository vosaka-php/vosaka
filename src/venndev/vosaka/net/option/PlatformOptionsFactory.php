<?php

declare(strict_types=1);

namespace venndev\vosaka\net\option;

use venndev\vosaka\utils\PlatformDetector;

final class PlatformOptionsFactory
{
    public static function createStreamOptions(): StreamOptions
    {
        $options = new StreamOptions();

        if (PlatformDetector::isWindows()) {
            // Windows specific optimizations
            $options->setBufferSize(65536)->setChunkSize(8192)->setTimeout(30);
        } elseif (PlatformDetector::isLinux()) {
            // Linux specific optimizations
            $options
                ->setBufferSize(131072)
                ->setChunkSize(16384)
                ->setTimeout(60);
        } elseif (PlatformDetector::isMacOS()) {
            // macOS specific optimizations
            $options->setBufferSize(65536)->setChunkSize(8192)->setTimeout(45);
        }

        return $options;
    }

    public static function createSocketOptions(): SocketOptions
    {
        $options = new SocketOptions();

        if (PlatformDetector::isWindows()) {
            // Windows specific optimizations
            $options
                ->setReuseAddress(true)
                ->setTcpNoDelay(true)
                ->setSendBufferSize(65536)
                ->setReceiveBufferSize(65536)
                ->setTimeout(30);
        } elseif (PlatformDetector::isLinux()) {
            // Linux specific optimizations
            $options
                ->setReuseAddress(true)
                ->setReusePort(true)
                ->setTcpNoDelay(true)
                ->setKeepAlive(true)
                ->setSendBufferSize(131072)
                ->setReceiveBufferSize(131072)
                ->setTimeout(60);
        } elseif (PlatformDetector::isMacOS()) {
            // macOS specific optimizations
            $options
                ->setReuseAddress(true)
                ->setTcpNoDelay(true)
                ->setKeepAlive(true)
                ->setSendBufferSize(65536)
                ->setReceiveBufferSize(65536)
                ->setTimeout(45);
        }

        return $options;
    }
}
