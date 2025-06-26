<?php

declare(strict_types=1);

namespace venndev\vosaka\utils;

use InvalidArgumentException;

/**
 * MemUtil class for memory-related utility functions and conversions.
 *
 * This utility class provides various methods for working with memory measurements
 * and conversions. It includes functions for converting between different memory
 * units and for retrieving current memory usage statistics from PHP's memory
 * management system.
 *
 * All memory conversion functions validate input to ensure non-negative values
 * and throw appropriate exceptions for invalid input. Memory usage functions
 * return current and peak memory usage in different units for monitoring
 * and debugging purposes.
 */
final class MemUtil
{
    /**
     * Convert a value to bytes by multiplying by 1024.
     *
     * Converts the input value to bytes assuming the input represents
     * kilobytes. This is a simple multiplication by 1024.
     *
     * @param int $value The value in kilobytes to convert to bytes
     * @return int The value converted to bytes
     * @throws InvalidArgumentException If the value is negative
     */
    public static function toB(int $value): int
    {
        if ($value < 0) {
            throw new InvalidArgumentException(
                "Value must be a non-negative integer."
            );
        }
        return $value * 1024;
    }

    /**
     * Convert a value from megabytes to bytes.
     *
     * Converts the input value from megabytes to bytes by multiplying
     * by 1024 * 1024 (1,048,576). This is useful for converting memory
     * limits specified in MB to the byte values used internally.
     *
     * @param int $value The value in megabytes to convert to bytes
     * @return int The value converted to bytes
     * @throws InvalidArgumentException If the value is negative
     */
    public static function toKB(int $value): int
    {
        if ($value < 0) {
            throw new InvalidArgumentException(
                "Value must be a non-negative integer."
            );
        }
        return $value * 1024 * 1024;
    }

    /**
     * Get the current memory usage in kilobytes.
     *
     * Returns the current memory usage of the PHP script in kilobytes.
     * Uses memory_get_usage(true) to get the real memory usage including
     * memory allocated by the system but not used by emalloc().
     *
     * @return int Current memory usage in kilobytes
     */
    public static function getKBUsed(): int
    {
        $memoryUsage = memory_get_usage(true);
        return (int) ($memoryUsage / 1024);
    }

    /**
     * Get the current memory usage in megabytes.
     *
     * Returns the current memory usage of the PHP script in megabytes.
     * Uses memory_get_usage(true) to get the real memory usage including
     * memory allocated by the system but not used by emalloc().
     *
     * @return int Current memory usage in megabytes
     */
    public static function getMBUsed(): int
    {
        $memoryUsage = memory_get_usage(true);
        return (int) ($memoryUsage / (1024 * 1024));
    }

    /**
     * Get the peak memory usage in kilobytes.
     *
     * Returns the peak memory usage of the PHP script in kilobytes since
     * the script started. Uses memory_get_peak_usage(true) to get the real
     * peak memory usage including memory allocated by the system.
     *
     * @return int Peak memory usage in kilobytes
     */
    public static function getKBPeak(): int
    {
        $peakMemoryUsage = memory_get_peak_usage(true);
        return (int) ($peakMemoryUsage / 1024);
    }

    /**
     * Get the peak memory usage in megabytes.
     *
     * Returns the peak memory usage of the PHP script in megabytes since
     * the script started. Uses memory_get_peak_usage(true) to get the real
     * peak memory usage including memory allocated by the system.
     *
     * @return int Peak memory usage in megabytes
     */
    public static function getMBPeak(): int
    {
        $peakMemoryUsage = memory_get_peak_usage(true);
        return (int) ($peakMemoryUsage / (1024 * 1024));
    }
}
