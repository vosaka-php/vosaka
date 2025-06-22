<?php

declare(strict_types=1);

namespace venndev\vosaka\fs;

use Generator;
use InvalidArgumentException;
use RuntimeException;

final class File
{
    public static function read(string $path): Generator
    {
        if (!file_exists($path)) {
            throw new InvalidArgumentException("File does not exist: $path");
        }

        $content = @fopen($path, 'rb');
        if (!$content) {
            throw new RuntimeException("Failed to open file: $path");
        }

        try {
            while (!feof($content)) {
                $chunk = fread($content, 8192);
                if ($chunk === false) {
                    throw new RuntimeException("Failed to read file: $path");
                }
                yield $chunk;
            }
        } finally {
            fclose($content);
        }
    }

    public static function write(string $path, string $data): Generator
    {
        $file = @fopen($path, 'wb');
        if (!$file) {
            throw new RuntimeException("Failed to open file for writing: $path");
        }

        try {
            $bytesWritten = fwrite($file, $data);
            if ($bytesWritten === false) {
                throw new RuntimeException("Failed to write to file: $path");
            }
            yield $bytesWritten;
        } finally {
            fclose($file);
        }
    }
}
