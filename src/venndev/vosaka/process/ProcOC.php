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

    /**
     * Cleans the output by trimming whitespace and quotes.
     *
     * @param string $output The output to clean.
     * @return string The cleaned output.
     */
    public static function clean(string $output): string
    {
        return trim($output, " \t\n\r\0\x0B\"'");
    }

    /**
     * Cleans the output with advanced options.
     *
     * @param string $output The output to clean.
     * @param array $options Options for cleaning:
     *   - 'remove_quotes': Whether to remove quotes (default: true).
     *   - 'trim_whitespace': Whether to trim whitespace (default: true).
     *   - 'remove_extra_newlines': Whether to remove extra newlines (default: false).
     *   - 'encoding': Encoding to convert the output to (optional).
     *   - 'normalize_line_endings': Whether to normalize line endings (default: false).
     * @return string The cleaned output.
     */
    public static function cleanAdvanced(
        string $output,
        array $options = []
    ): string {
        $result = $output;

        // Normalize line endings first (useful for cross-platform)
        if ($options["normalize_line_endings"] ?? false) {
            $result = self::normalizeLineEndings($result);
        }

        if ($options["remove_quotes"] ?? true) {
            $result = trim($result, '"\'');
        }

        if ($options["trim_whitespace"] ?? true) {
            $result = trim($result);
        }

        if ($options["remove_extra_newlines"] ?? false) {
            $result = preg_replace('/\n+/', "\n", $result);
        }

        if (isset($options["encoding"])) {
            $result = mb_convert_encoding($result, $options["encoding"]);
        }

        return $result;
    }

    /**
     * Cleans the output and splits it into lines.
     *
     * @param string $output The output to clean.
     * @return array An array of cleaned lines.
     */
    public static function cleanLines(string $output): array
    {
        $output = self::normalizeLineEndings($output);
        $lines = explode("\n", $output);
        return array_map([self::class, "clean"], array_filter($lines));
    }

    /**
     * Cleans the output and decodes it as JSON.
     *
     * @param string $output The output to clean and decode.
     * @return array|string The decoded JSON as an associative array, or the cleaned string if decoding fails.
     */
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
