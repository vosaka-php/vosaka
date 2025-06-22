<?php

declare(strict_types=1);

namespace venndev\vosaka\utils;

use InvalidArgumentException;

final class MemUtil
{

    public static function toB(int $value): int
    {
        if ($value < 0) {
            throw new InvalidArgumentException('Value must be a non-negative integer.');
        }
        return $value * 1024;
    }

    public static function toKB(int $value): int
    {
        if ($value < 0) {
            throw new InvalidArgumentException('Value must be a non-negative integer.');
        }
        return $value * 1024 * 1024;
    }

    public static function getKBUsed(): int
    {
        $memoryUsage = memory_get_usage(true);
        return (int) ($memoryUsage / 1024);
    }

    public static function getMBUsed(): int
    {
        $memoryUsage = memory_get_usage(true);
        return (int) ($memoryUsage / (1024 * 1024));
    }

    public static function getKBPeak(): int
    {
        $peakMemoryUsage = memory_get_peak_usage(true);
        return (int) ($peakMemoryUsage / 1024);
    }

    public static function getMBPeak(): int
    {
        $peakMemoryUsage = memory_get_peak_usage(true);
        return (int) ($peakMemoryUsage / (1024 * 1024));
    }
}