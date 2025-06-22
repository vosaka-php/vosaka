<?php

declare(strict_types=1);

namespace venndev\vosaka\process;

final class Stdio
{
    public static function piped(): array
    {
        return [
            0 => ['pipe', 'r'], // stdin
            1 => ['pipe', 'w'], // stdout
            2 => ['pipe', 'w'], // stderr
        ];
    }

    public static function null(): array
    {
        $nullDevice = self::getNullDevice();
        return [
            0 => ['file', $nullDevice, 'r'],
            1 => ['file', $nullDevice, 'w'],
            2 => ['file', $nullDevice, 'w'],
        ];
    }

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
        return PHP_OS_FAMILY === 'Windows' ? 'NUL' : '/dev/null';
    }

    public static function isWindows(): bool
    {
        return PHP_OS_FAMILY === 'Windows';
    }
}