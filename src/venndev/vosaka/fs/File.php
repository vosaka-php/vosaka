<?php

declare(strict_types=1);

namespace venndev\vosaka\fs;

use Generator;
use venndev\vosaka\VOsaka;
use venndev\vosaka\fs\exceptions\FileNotFoundException;
use venndev\vosaka\fs\exceptions\FileIOException;

/**
 * File class for asynchronous file operations.
 *
 * Provides asynchronous file I/O operations that work with the VOsaka event loop.
 * All operations are designed to be non-blocking and yield control appropriately
 * to allow other tasks to execute while file operations are in progress.
 *
 * The class uses chunked reading for large files and atomic write operations
 * with temporary files to ensure data integrity.
 */
final class File
{
    /**
     * Asynchronously read a file in chunks.
     *
     * Reads the specified file in 8KB chunks, yielding each chunk as it's read.
     * This approach prevents memory exhaustion when reading large files and
     * allows other tasks to execute between chunks. The file is automatically
     * closed after reading completes or if an error occurs.
     *
     * @param string $path The path to the file to read
     * @return Generator<string> Yields string chunks of the file content
     * @throws InvalidArgumentException If the file does not exist
     * @throws RuntimeException If the file cannot be opened or read
     */
    public static function read(string $path): Generator
    {
        if (!file_exists($path)) {
            throw FileNotFoundException::forFile($path, "read");
        }

        $content = @fopen($path, "rb");
        if (!$content) {
            throw FileIOException::forOpen($path, "rb");
        }

        try {
            while (!feof($content)) {
                $chunk = fread($content, 8192);
                if ($chunk === false) {
                    throw FileIOException::forRead($path, 8192);
                }
                yield $chunk;
            }
        } finally {
            fclose($content);
        }
    }

    /**
     * Asynchronously write data to a file with atomic operations.
     *
     * Writes data to a file using a temporary file approach to ensure atomicity.
     * The data is first written to a temporary file, then atomically renamed to
     * the target path. This prevents data corruption if the write operation is
     * interrupted. The temporary file is registered with the graceful shutdown
     * manager for cleanup.
     *
     * The operation includes proper file synchronization (fsync) to ensure data
     * is written to disk before the operation completes.
     *
     * @param string $path The path where the file should be written
     * @param string $data The data to write to the file
     * @return Generator<int> Yields the number of bytes written
     * @throws RuntimeException If the file cannot be opened, written to, or renamed
     */
    public static function write(string $path, string $data): Generator
    {
        $tempPath = $path . ".tmp." . uniqid();

        $file = @fopen($tempPath, "wb");
        if (!$file) {
            throw FileIOException::forOpen($tempPath, "wb");
        }

        VOsaka::getLoop()->getGracefulShutdown()->addTempFile($tempPath);

        try {
            $bytesWritten = fwrite($file, $data);
            if ($bytesWritten === false) {
                throw FileIOException::forWrite($tempPath, strlen($data));
            }

            fflush($file);
            fsync($file);

            yield $bytesWritten;
        } finally {
            @fclose($file);

            if (file_exists($tempPath)) {
                if (!rename($tempPath, $path)) {
                    unlink($tempPath);
                    throw FileIOException::forMove($tempPath, $path);
                }

                VOsaka::getLoop()->getGracefulShutdown()->removeTempFile($path);
            }
        }
    }
}
