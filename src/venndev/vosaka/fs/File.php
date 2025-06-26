<?php

declare(strict_types=1);

namespace venndev\vosaka\fs;

use Generator;
use InvalidArgumentException;
use RuntimeException;
use venndev\vosaka\VOsaka;

final class File
{
    /**
     * Reads a file in chunks and yields each chunk.
     *
     * @param string $path The path to the file.
     * @return Generator<string> Yields chunks of the file content.
     * @throws InvalidArgumentException If the file does not exist.
     * @throws RuntimeException If the file cannot be opened or read.
     */
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

    /**
     * Writes data to a file.
     *
     * @param string $path The path to the file.
     * @param string $data The data to write.
     * @return Generator<int> Yields the number of bytes written.
     * @throws RuntimeException If the file cannot be opened or written to.
     */
    public static function write(string $path, string $data): Generator
    {
        $tempPath = $path . '.tmp.' . uniqid();

        $file = @fopen($tempPath, 'wb');
        if (!$file) {
            throw new RuntimeException("Failed to open temp file for writing: $tempPath");
        }
        VOsaka::getLoop()->getGracefulShutdown()->addTempFile($tempPath);

        try {
            $bytesWritten = fwrite($file, $data);
            if ($bytesWritten === false) {
                throw new RuntimeException("Failed to write to temp file: $tempPath");
            }

            fflush($file);
            fsync($file);

            yield $bytesWritten;
        } finally {
            fclose($file);

            if (file_exists($tempPath)) {
                if (!rename($tempPath, $path)) {
                    unlink($tempPath);
                    throw new RuntimeException("Failed to rename temp file to final path");
                }

                VOsaka::getLoop()->getGracefulShutdown()->cleanup();
            }
        }
    }
}
