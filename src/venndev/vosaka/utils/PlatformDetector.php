<?php

declare(strict_types=1);

namespace venndev\vosaka\utils;

final class PlatformDetector
{
    public static function isWindows(): bool
    {
        return strtoupper(substr(PHP_OS, 0, 3)) === "WIN";
    }

    public static function isLinux(): bool
    {
        return PHP_OS_FAMILY === "Linux";
    }

    public static function isMacOS(): bool
    {
        return PHP_OS_FAMILY === "Darwin";
    }

    public static function isUnix(): bool
    {
        return in_array(PHP_OS_FAMILY, ["Linux", "Darwin", "BSD", "Solaris"]);
    }

    public static function getPlatform(): string
    {
        return PHP_OS_FAMILY;
    }
}
