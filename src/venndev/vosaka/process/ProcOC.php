<?php

declare(strict_types=1);

namespace venndev\vosaka\process;

final class ProcOC
{
    public const REMOVE_QUOTES = "remove_quotes";
    public const TRIM_WHITESPACE = "trim_whitespace";
    public const REMOVE_EXTRA_NEWLINES = "remove_extra_newlines";
    public const ENCODING = "encoding";
    public const NORMALIZE_LINE_ENDINGS = "normalize_line_endings";

    public static function clean(string $output): string
    {
        return trim($output, " \t\n\r\0\x0B\"'");
    }

    public static function cleanAdvanced(string $output, array $options = []): string
    {
        $result = $output;

        // Normalize line endings first (useful for cross-platform)
        if ($options['normalize_line_endings'] ?? false) {
            $result = self::normalizeLineEndings($result);
        }

        if ($options['remove_quotes'] ?? true) {
            $result = trim($result, '"\'');
        }

        if ($options['trim_whitespace'] ?? true) {
            $result = trim($result);
        }

        if ($options['remove_extra_newlines'] ?? false) {
            $result = preg_replace('/\n+/', "\n", $result);
        }

        if (isset($options['encoding'])) {
            $result = mb_convert_encoding($result, $options['encoding']);
        }

        return $result;
    }

    public static function cleanLines(string $output): array
    {
        // Handle different line endings
        $output = self::normalizeLineEndings($output);
        $lines = explode("\n", $output);
        return array_map([self::class, 'clean'], array_filter($lines));
    }

    public static function cleanJson(string $output): array|string
    {
        $cleaned = self::clean($output);

        $decoded = json_decode($cleaned, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        return $cleaned;
    }

    private static function normalizeLineEndings(string $text): string
    {
        // Convert Windows (\r\n) and Mac (\r) line endings to Unix (\n)
        return str_replace(["\r\n", "\r"], "\n", $text);
    }
}