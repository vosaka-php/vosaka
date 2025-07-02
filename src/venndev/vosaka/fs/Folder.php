<?php

declare(strict_types=1);

namespace venndev\vosaka\fs;

use DirectoryIterator;
use Exception;
use Generator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use venndev\vosaka\core\Result;
use venndev\vosaka\core\Future;
use venndev\vosaka\VOsaka;
use venndev\vosaka\fs\exceptions\DirectoryException;
use venndev\vosaka\fs\exceptions\FileIOException;
use venndev\vosaka\fs\exceptions\InvalidPathException;
use venndev\vosaka\fs\exceptions\LockException;
use venndev\vosaka\time\Sleep;

/**
 * Provides comprehensive directory manipulation functions with async/await patterns,
 * proper resource management, and graceful shutdown integration. All operations
 * that involve streams, temporary files, or long-running processes use GracefulShutdown
 * for proper cleanup.
 */
final class Folder
{
    private const DEFAULT_CHUNK_SIZE = 8192;
    private const DEFAULT_PERMISSIONS = 0755;
    private const LOCK_TIMEOUT_SECONDS = 30.0;

    /**
     * Asynchronously create a directory with all parent directories.
     *
     * This function creates the specified
     * directory and all necessary parent directories. Operations are chunked to
     * allow other tasks to execute.
     *
     * @param string $path The directory path to create
     * @param int $permissions The permissions to set (default: 0755)
     * @param bool $recursive Whether to create parent directories (default: true)
     * @return Result<bool> Returns true on success
     * @throws DirectoryException If directory creation fails
     */
    public static function createDir(
        string $path,
        int $permissions = self::DEFAULT_PERMISSIONS,
        bool $recursive = true
    ): Result {
        $fn = function () use ($path, $permissions, $recursive): Generator {
            if (empty($path)) {
                throw InvalidPathException::forPath(
                    $path,
                    "Directory path cannot be empty"
                );
            }

            if (is_dir($path)) {
                yield true;
                return true;
            }

            // Yield to allow other tasks to run
            yield Sleep::us(100);

            $success = $recursive ? @mkdir($path, $permissions, true) : @mkdir($path, $permissions);

            if (! $success) {
                $error = error_get_last();
                throw DirectoryException::forCreation(
                    $path,
                    $error["message"] ?? "Unknown error"
                );
            }

            yield true;
            return true;
        };

        return Future::new($fn());
    }

    /**
     * Asynchronously remove a directory and its contents.
     *
     * Recursively removes the directory
     * and all its contents. Uses GracefulShutdown to track temporary operations.
     *
     * @param string $path The directory path to remove
     * @param bool $recursive Whether to remove contents recursively (default: true)
     * @return Result<bool> Returns true on success
     * @throws DirectoryException If directory removal fails
     */
    public static function removeDir(
        string $path,
        bool $recursive = true
    ): Result {
        $fn = function () use ($path, $recursive): Generator {
            if (! is_dir($path)) {
                throw DirectoryException::forNotFound($path);
            }

            if (! $recursive) {
                $success = @rmdir($path);
                if (! $success) {
                    $error = error_get_last();
                    throw DirectoryException::forRemoval(
                        $path,
                        $error["message"] ?? "Unknown error"
                    );
                }
                yield true;
                return true;
            }

            // For recursive removal, use a generator to process files in chunks
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $path,
                    RecursiveDirectoryIterator::SKIP_DOTS
                ),
                RecursiveIteratorIterator::CHILD_FIRST
            );

            $count = 0;
            foreach ($iterator as $file) {
                if ($file->isDir()) {
                    $success = @rmdir($file->getPathname());
                } else {
                    $success = @unlink($file->getPathname());
                }

                if (! $success) {
                    $error = error_get_last();
                    throw DirectoryException::forRemoval(
                        $file->getPathname(),
                        $error["message"] ?? "Unknown error"
                    );
                }

                // Yield periodically to allow other tasks
                if (++$count % 100 === 0) {
                    yield Sleep::us(100);
                }
            }

            $success = @rmdir($path);
            if (! $success) {
                $error = error_get_last();
                throw DirectoryException::forRemoval(
                    $path,
                    $error["message"] ?? "Unknown error"
                );
            }

            yield true;
            return true;
        };

        return Future::new($fn());
    }

    /**
     * Asynchronously read directory entries.
     *
     * Returns an async iterator over directory entries.
     * Yields each entry to allow for non-blocking iteration over large directories.
     *
     * @param string $path The directory path to read
     * @param bool $includeHidden Whether to include hidden files (default: false)
     * @return Result<SplFileInfo> Yields SplFileInfo objects for each entry
     * @throws DirectoryException If directory cannot be read
     */
    public static function readDir(
        string $path,
        bool $includeHidden = false
    ): Result {
        $fn = function () use ($path, $includeHidden): Generator {
            if (! is_dir($path)) {
                throw DirectoryException::forNotFound($path);
            }

            if (! is_readable($path)) {
                throw DirectoryException::forPermission($path, "read");
            }

            try {
                $iterator = new DirectoryIterator($path);
                $count = 0;

                foreach ($iterator as $entry) {
                    if ($entry->isDot()) {
                        continue;
                    }

                    if (! $includeHidden && $entry->getFilename()[0] === ".") {
                        continue;
                    }

                    yield $entry->getFileInfo();

                    // Yield control periodically
                    if (++$count % 50 === 0) {
                        yield Sleep::us(100);
                    }
                }
            } catch (Exception $e) {
                throw DirectoryException::forRead($path, [
                    "error" => $e->getMessage(),
                ]);
            }
        };

        return Future::new($fn());
    }

    /**
     * Asynchronously walk directory tree recursively.
     *
     * Recursively traverses the directory
     * tree and yields each file/directory encountered. Supports filtering and
     * depth limits.
     *
     * @param string $path The root directory to walk
     * @param int $maxDepth Maximum depth to traverse (-1 for unlimited)
     * @param callable|null $filter Optional filter function for entries
     * @return Result<SplFileInfo> Yields SplFileInfo objects for each entry
     * @throws DirectoryException If directory cannot be walked
     */
    public static function walkDir(
        string $path,
        int $maxDepth = -1,
        ?callable $filter = null
    ): Result {
        $fn = function () use ($path, $maxDepth, $filter): Generator {
            if (! is_dir($path)) {
                throw DirectoryException::forNotFound($path);
            }

            try {
                $iterator = new RecursiveDirectoryIterator(
                    $path,
                    RecursiveDirectoryIterator::SKIP_DOTS
                );

                $walker = new RecursiveIteratorIterator($iterator);

                if ($maxDepth >= 0) {
                    $walker->setMaxDepth($maxDepth);
                }

                $count = 0;

                foreach ($walker as $entry) {
                    $fileInfo = $entry->getFileInfo();

                    // Apply filter if provided
                    if ($filter !== null && ! $filter($fileInfo)) {
                        continue;
                    }

                    yield $fileInfo;

                    // Yield control periodically
                    if (++$count % 100 === 0) {
                        yield Sleep::us(100);
                    }
                }
            } catch (Exception $e) {
                throw DirectoryException::forWalk($path, $e->getMessage(), [
                    "error" => $e->getMessage(),
                ]);
            }
        };

        return Future::new($fn());
    }

    /**
     * Asynchronously copy directory and its contents.
     *
     * Copies the entire directory
     * tree from source to destination. Uses temporary files and GracefulShutdown
     * for proper resource management.
     *
     * @param string $source Source directory path
     * @param string $destination Destination directory path
     * @param bool $overwrite Whether to overwrite existing files (default: false)
     * @return Result<int> Returns number of files copied
     * @throws DirectoryException If copy operation fails
     */
    public static function copyDir(
        string $source,
        string $destination,
        bool $overwrite = false
    ): Result {
        $fn = function () use ($source, $destination, $overwrite): Generator {
            if (! is_dir($source)) {
                throw DirectoryException::forNotFound($source);
            }

            $gracefulShutdown = VOsaka::getLoop()->getGracefulShutdown();
            $copiedCount = 0;

            // Create destination directory
            yield from self::createDir($destination);

            // Walk through source directory
            $walker = self::walkDir($source);

            foreach ($walker as $sourceFile) {
                $relativePath = substr(
                    $sourceFile->getPathname(),
                    strlen($source) + 1
                );
                $destinationPath =
                    $destination.DIRECTORY_SEPARATOR.$relativePath;

                if ($sourceFile->isDir()) {
                    yield from self::createDir($destinationPath);
                } else {
                    // Create parent directory if needed
                    $parentDir = dirname($destinationPath);
                    if (! is_dir($parentDir)) {
                        yield from self::createDir($parentDir);
                    }

                    // Check if destination exists and overwrite is disabled
                    if (! $overwrite && file_exists($destinationPath)) {
                        continue;
                    }

                    // Copy file using temporary file for atomicity
                    $tempPath = $destinationPath.".tmp.".uniqid();
                    $gracefulShutdown->addTempFile($tempPath);

                    try {
                        $success = copy($sourceFile->getPathname(), $tempPath);
                        if (! $success) {
                            throw FileIOException::forCopy(
                                $sourceFile->getPathname(),
                                $tempPath
                            );
                        }

                        if (! rename($tempPath, $destinationPath)) {
                            @unlink($tempPath);
                            throw FileIOException::forMove(
                                $tempPath,
                                $destinationPath
                            );
                        }

                        $gracefulShutdown->removeTempFile($tempPath);
                        $copiedCount++;
                    } catch (Exception $e) {
                        @unlink($tempPath);
                        $gracefulShutdown->removeTempFile($tempPath);
                        throw $e;
                    }

                    // Yield control periodically
                    if ($copiedCount % 50 === 0) {
                        yield Sleep::us(100);
                    }
                }

                yield;
            }

            yield $copiedCount;
            return $copiedCount;
        };

        return Future::new($fn());
    }

    /**
     * Asynchronously move/rename directory.
     *
     * Moves or renames a directory atomically
     * when possible, or falls back to copy+delete for cross-filesystem moves.
     *
     * @param string $source Source directory path
     * @param string $destination Destination directory path
     * @return Result<bool> Returns true on success
     * @throws DirectoryException If move operation fails
     */
    public static function moveDir(
        string $source,
        string $destination
    ): Result {
        $fn = function () use ($source, $destination): Generator {
            if (! is_dir($source)) {
                throw DirectoryException::forNotFound($source);
            }

            if (is_dir($destination)) {
                throw DirectoryException::forExists($destination);
            }

            // Try atomic rename first
            $success = @rename($source, $destination);
            if ($success) {
                yield true;
                return true;
            }

            // Fall back to copy + delete for cross-filesystem moves
            $copiedCount = yield from self::copyDir($source, $destination);
            yield from self::removeDir($source);

            yield true;
            return true;
        };

        return Future::new($fn());
    }

    /**
     * Asynchronously watch directory for changes.
     *
     * Monitors directory for changes
     * and yields events. Uses polling with configurable intervals.
     *
     * @param string $path Directory path to watch
     * @param float $pollInterval Polling interval in seconds (default: 1.0)
     * @param callable|null $filter Optional filter for change events
     * @return Result<array> Yields change events as arrays
     * @throws DirectoryException If directory cannot be watched
     */
    public static function watchDir(
        string $path,
        float $pollInterval = 1.0,
        ?callable $filter = null
    ): Result {
        $fn = function () use ($path, $pollInterval, $filter): Generator {
            if (! is_dir($path)) {
                throw DirectoryException::forNotFound($path);
            }

            $lastSnapshot = [];

            // Create initial snapshot
            $walker = self::walkDir($path);
            foreach ($walker as $file) {
                $lastSnapshot[$file->getPathname()] = [
                    "mtime" => $file->getMTime(),
                    "size" => $file->isFile() ? $file->getSize() : 0,
                    "type" => $file->isDir() ? "dir" : "file",
                ];
                yield;
            }

            while (true) {
                yield from Sleep::new($pollInterval)->toGenerator();

                $currentSnapshot = [];
                $walker = self::walkDir($path);

                foreach ($walker as $file) {
                    $currentSnapshot[$file->getPathname()] = [
                        "mtime" => $file->getMTime(),
                        "size" => $file->isFile() ? $file->getSize() : 0,
                        "type" => $file->isDir() ? "dir" : "file",
                    ];
                    yield;
                }

                // Detect changes
                $events = [];

                // Check for new or modified files
                foreach ($currentSnapshot as $filepath => $info) {
                    if (! isset($lastSnapshot[$filepath])) {
                        $events[] = [
                            "type" => "created",
                            "path" => $filepath,
                            "info" => $info,
                        ];
                    } elseif (
                        $lastSnapshot[$filepath]["mtime"] !== $info["mtime"] ||
                        $lastSnapshot[$filepath]["size"] !== $info["size"]
                    ) {
                        $events[] = [
                            "type" => "modified",
                            "path" => $filepath,
                            "info" => $info,
                        ];
                    }
                }

                // Check for deleted files
                foreach ($lastSnapshot as $filepath => $info) {
                    if (! isset($currentSnapshot[$filepath])) {
                        $events[] = [
                            "type" => "deleted",
                            "path" => $filepath,
                            "info" => $info,
                        ];
                    }
                }

                // Yield events
                foreach ($events as $event) {
                    if ($filter === null || $filter($event)) {
                        yield $event;
                    }
                }

                $lastSnapshot = $currentSnapshot;
            }
        };

        return Future::new($fn());
    }

    /**
     * Asynchronously create temporary directory.
     *
     * Creates a temporary directory with unique name and registers it with
     * GracefulShutdown for automatic cleanup.
     *
     * @param string|null $prefix Optional prefix for directory name
     * @param string|null $tempDir Base temporary directory (default: system temp)
     * @return Result<string> Returns path to created temporary directory
     * @throws DirectoryException If temporary directory creation fails
     */
    public static function createTempDir(
        ?string $prefix = null,
        ?string $tempDir = null
    ): Result {
        $fn = function () use ($prefix, $tempDir): Generator {
            $tempDir ??= sys_get_temp_dir();
            $prefix ??= "vosaka_";

            $attempts = 0;
            $maxAttempts = 10;

            while ($attempts < $maxAttempts) {
                $tempPath =
                    $tempDir.
                    DIRECTORY_SEPARATOR.
                    $prefix.
                    uniqid().
                    "_".
                    mt_rand(1000, 9999);

                if (! file_exists($tempPath)) {
                    yield from self::createDir($tempPath);

                    // Register with GracefulShutdown for cleanup
                    VOsaka::getLoop()
                        ->getGracefulShutdown()
                        ->addCleanupCallback(function () use ($tempPath) {
                            if (is_dir($tempPath)) {
                                self::removeDirSync($tempPath);
                            }
                        });

                    yield $tempPath;
                    return $tempPath;
                }

                $attempts++;
                yield Sleep::us(100);
            }

            throw DirectoryException::forCreation(
                $tempPath ?? $tempDir,
                "Could not create unique temporary directory"
            );
        };

        return Future::new($fn());
    }

    /**
     * Asynchronously lock directory for exclusive access.
     *
     * Creates a lock file in the directory to prevent concurrent access.
     * Uses GracefulShutdown to ensure lock cleanup.
     *
     * @param string $path Directory path to lock
     * @param float $timeout Timeout in seconds for acquiring lock
     * @return Result<resource> Returns lock file handle
     * @throws LockException If lock cannot be acquired
     */
    public static function lockDir(
        string $path,
        float $timeout = self::LOCK_TIMEOUT_SECONDS
    ): Result {
        $fn = function () use ($path, $timeout): Generator {
            if (! is_dir($path)) {
                throw DirectoryException::forNotFound($path);
            }

            $lockFile = $path.DIRECTORY_SEPARATOR.".vosaka_lock";
            $gracefulShutdown = VOsaka::getLoop()->getGracefulShutdown();
            $startTime = microtime(true);

            while (microtime(true) - $startTime < $timeout) {
                $handle = @fopen($lockFile, "x");
                if ($handle !== false) {
                    // Register lock file for cleanup
                    $gracefulShutdown->addTempFile($lockFile);

                    // Write lock information
                    fwrite(
                        $handle,
                        json_encode([
                            "pid" => getmypid(),
                            "timestamp" => time(),
                            "path" => $path,
                        ])
                    );
                    fflush($handle);

                    yield $handle;
                    return $handle;
                }

                yield Sleep::ms(100);
            }

            throw LockException::forTimeout($path, $timeout);
        };

        return Future::new($fn());
    }

    /**
     * Release directory lock.
     *
     * @param resource $lockHandle Lock handle returned by lockDir()
     * @param string $path Directory path that was locked
     * @return Result<bool> Returns true on success
     */
    public static function unlockDir($lockHandle, string $path): Result
    {
        $fn = function () use ($lockHandle, $path): Generator {
            if (is_resource($lockHandle)) {
                fclose($lockHandle);
            }

            $lockFile = $path.DIRECTORY_SEPARATOR.".vosaka_lock";
            if (file_exists($lockFile)) {
                @unlink($lockFile);
                VOsaka::getLoop()->getGracefulShutdown()->removeTempFile($lockFile);
            }

            yield true;
            return true;
        };

        return Future::new($fn());
    }

    /**
     * Get directory metadata asynchronously.
     *
     * @param string $path Directory path
     * @return Result<array> Returns directory metadata
     * @throws DirectoryException If directory cannot be accessed
     */
    public static function metadata(string $path): Result
    {
        $fn = function () use ($path): Generator {
            if (! is_dir($path)) {
                throw DirectoryException::forNotFound($path);
            }

            $stat = @stat($path);
            if ($stat === false) {
                throw DirectoryException::forStat($path);
            }

            $metadata = [
                "path" => realpath($path),
                "size" => $stat["size"],
                "permissions" => $stat["mode"] & 0777,
                "owner" => $stat["uid"],
                "group" => $stat["gid"],
                "accessed" => $stat["atime"],
                "modified" => $stat["mtime"],
                "created" => $stat["ctime"],
                "is_readable" => is_readable($path),
                "is_writable" => is_writable($path),
                "is_executable" => is_executable($path),
            ];

            yield $metadata;
            return $metadata;
        };

        return Future::new($fn());
    }

    /**
     * Synchronous helper method for cleanup operations.
     * Used internally by GracefulShutdown callbacks.
     */
    private static function removeDirSync(string $path): bool
    {
        if (! is_dir($path)) {
            return true;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $path,
                RecursiveDirectoryIterator::SKIP_DOTS
            ),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isDir()) {
                @rmdir($file->getPathname());
            } else {
                @unlink($file->getPathname());
            }
        }

        return @rmdir($path);
    }

    /**
     * Asynchronously calculate directory size.
     *
     * @param string $path Directory path
     * @return Result<int> Returns total size in bytes
     * @throws DirectoryException If directory cannot be accessed
     */
    public static function calculateSize(string $path): Result
    {
        $fn = function () use ($path): Generator {
            if (! is_dir($path)) {
                throw DirectoryException::forNotFound($path);
            }

            $totalSize = 0;
            $count = 0;

            $walker = self::walkDir($path);
            foreach ($walker as $file) {
                if ($file->isFile()) {
                    $totalSize += $file->getSize();
                }

                // Yield control periodically
                if (++$count % 100 === 0) {
                    yield Sleep::us(100);
                }
            }

            yield $totalSize;
            return $totalSize;
        };

        return Future::new($fn());
    }

    /**
     * Asynchronously find files matching pattern.
     *
     * @param string $path Directory path to search in
     * @param string $pattern Glob pattern to match
     * @param bool $recursive Whether to search recursively
     * @return Result<SplFileInfo> Yields matching files
     * @throws DirectoryException If directory cannot be searched
     */
    public static function find(
        string $path,
        string $pattern,
        bool $recursive = true
    ): Result {
        $fn = function () use ($path, $pattern, $recursive): Generator {
            if (! is_dir($path)) {
                throw DirectoryException::forNotFound($path);
            }

            $count = 0;

            if ($recursive) {
                $walker = self::walkDir($path);
                foreach ($walker as $file) {
                    if (fnmatch($pattern, $file->getFilename())) {
                        yield $file;
                    }

                    if (++$count % 50 === 0) {
                        yield Sleep::us(100);
                    }
                }
            } else {
                $reader = self::readDir($path);
                foreach ($reader as $file) {
                    if (fnmatch($pattern, $file->getFilename())) {
                        yield $file;
                    }

                    if (++$count % 50 === 0) {
                        yield Sleep::us(100);
                    }
                }
            }
        };

        return Future::new($fn());
    }
}
