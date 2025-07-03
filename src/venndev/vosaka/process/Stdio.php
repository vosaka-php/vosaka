<?php

declare(strict_types=1);

namespace venndev\vosaka\process;

use venndev\vosaka\utils\PlatformDetector;

final class Stdio
{
    /**
     * Provides standard input/output/error streams for process execution.
     *
     * @return array<int, array<string, mixed>> An array defining the standard streams.
     */
    public static function piped(): array
    {
        return [
            0 => ["pipe", "r"], // stdin
            1 => ["pipe", "w"], // stdout
            2 => ["pipe", "w"], // stderr
        ];
    }

    /**
     * Provides standard input/output/error streams that are not piped.
     *
     * @return array<int, array<string, mixed>> An array defining the standard streams.
     */
    public static function null(): array
    {
        $nullDevice = self::getNullDevice();
        return [
            0 => ["file", $nullDevice, "r"],
            1 => ["file", $nullDevice, "w"],
            2 => ["file", $nullDevice, "w"],
        ];
    }

    /**
     * Provides standard input/output/error streams that inherit from the parent process.
     *
     * @return array<int, mixed> An array defining the standard streams.
     */
    public static function inherit(): array
    {
        return [
            0 => STDIN,
            1 => STDOUT,
            2 => STDERR,
        ];
    }

    private static function getNullDevice(): string
    {
        return PlatformDetector::isWindows() ? "NUL" : "/dev/null";
    }

    /**
     * Checks if the current platform is Windows.
     *
     * @return bool Returns true if the platform is Windows, false otherwise.
     */
    public static function isWindows(): bool
    {
        return PlatformDetector::isWindows();
    }
}
