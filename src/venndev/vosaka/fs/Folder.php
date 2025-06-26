<?php

declare(strict_types=1);

namespace venndev\vosaka\fs;

use DirectoryIterator;
use Exception;
use Generator;
use InvalidArgumentException;
use RuntimeException;
use venndev\vosaka\core\Result;
use venndev\vosaka\VOsaka;
use venndev\vosaka\cleanup\GracefulShutdown;

final class Folder
{
    private static array $operationLocks = [];
    private static string $lockDir = '';
    private static bool $isShutdownHandlerRegistered = false;

    private static function registerShutdownHandler(): void
    {
        if (!self::$isShutdownHandlerRegistered) {
            register_shutdown_function([self::class, 'finalCleanup']);
            self::$isShutdownHandlerRegistered = true;
        }
    }

    private static function initLockDir(): void
    {
        if (empty(self::$lockDir)) {
            self::$lockDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'vosaka_locks';
            if (!is_dir(self::$lockDir)) {
                @mkdir(self::$lockDir, 0755, true);
            }
        }
    }

    private static function acquireLock(string $operation, string $path): string
    {
        self::initLockDir();

        $lockFile = self::$lockDir . DIRECTORY_SEPARATOR . md5($operation . $path) . '.lock';

        $handle = fopen($lockFile, 'w');
        if (!$handle || !flock($handle, LOCK_EX | LOCK_NB)) {
            throw new RuntimeException("Cannot acquire lock for operation: $operation on $path");
        }

        fwrite($handle, getmypid() . "\n" . time() . "\n" . $operation . "\n" . $path);
        fflush($handle);

        self::$operationLocks[$lockFile] = $handle;

        VOsaka::getLoop()->getGracefulShutDown()->addTempFile($lockFile);
        VOsaka::getLoop()->getGracefulShutDown()->addCleanupCallback(function () use ($lockFile) {
            self::releaseLock($lockFile);
        });

        return $lockFile;
    }

    private static function releaseLock(string $lockFile): void
    {
        if (isset(self::$operationLocks[$lockFile])) {
            $handle = self::$operationLocks[$lockFile];
            flock($handle, LOCK_UN);
            fclose($handle);
            @unlink($lockFile);
            unset(self::$operationLocks[$lockFile]);
        }
    }

    private static function createBackup(string $path): ?string
    {
        if (!file_exists($path)) {
            return null;
        }

        $backupPath = $path . '.backup.' . time() . '.' . getmypid();

        if (is_dir($path)) {
            $result = self::copy($path, $backupPath);
            if ($result->isOk()) {
                VOsaka::getLoop()->getGracefulShutDown()->addTempFile($backupPath);
                return $backupPath;
            }
            return null;
        } else {
            if (@copy($path, $backupPath)) {
                VOsaka::getLoop()->getGracefulShutDown()->addTempFile($backupPath);
                return $backupPath;
            }
            return null;
        }
    }

    private static function recursiveDelete(string $path): void
    {
        if (!is_dir($path)) {
            @unlink($path);
            return;
        }

        try {
            $iterator = new DirectoryIterator($path);
            foreach ($iterator as $fileinfo) {
                if ($fileinfo->isDot()) {
                    continue;
                }

                if ($fileinfo->isDir()) {
                    self::recursiveDelete($fileinfo->getPathname());
                } else {
                    @unlink($fileinfo->getPathname());
                }
            }
            @rmdir($path);
        } catch (Exception $e) {
            // Silent cleanup
        }
    }

    private static function restoreFromBackup(string $originalPath, string $backupPath): bool
    {
        if (!file_exists($backupPath)) {
            return false;
        }

        if (file_exists($originalPath)) {
            if (is_dir($originalPath)) {
                self::delete($originalPath);
            } else {
                @unlink($originalPath);
            }
        }

        if (is_dir($backupPath)) {
            $result = self::copy($backupPath, $originalPath);
            self::recursiveDelete($backupPath);
            return $result->isOk();
        } else {
            $success = @copy($backupPath, $originalPath);
            @unlink($backupPath);
            return $success;
        }
    }

    private static function validatePath(string $path): void
    {
        $realPath = realpath(dirname($path));
        if ($realPath === false) {
            throw new InvalidArgumentException("Invalid path: $path");
        }

        $dangerousPaths = [
            '/etc',
            '/usr',
            '/var',
            '/bin',
            '/sbin',
            '/lib',
            '/boot',
            'C:\\Windows',
            'C:\\Program Files',
            'C:\\System'
        ];

        foreach ($dangerousPaths as $dangerous) {
            if (strpos($realPath, $dangerous) === 0) {
                throw new InvalidArgumentException("Cannot operate on system path: $path");
            }
        }
    }

    private static function createTempFile(string $destinationPath): string
    {
        $tempFile = $destinationPath . '.tmp.' . getmypid() . '.' . uniqid();

        // Let GracefulShutdown handle temp file cleanup
        VOsaka::getLoop()->getGracefulShutDown()->addTempFile($tempFile);

        return $tempFile;
    }

    /**
     * Copies a directory from source to destination.
     * @param string $source The source directory path.
     * @param string $destination The destination directory path.
     * @return Result<bool> Returns true if the copy was successful, false otherwise.
     * @throws InvalidArgumentException If the source or destination paths are invalid.
     * @throws RuntimeException If the copy operation fails.
     */
    public static function copy(string $source, string $destination): Result
    {
        $fn = function () use ($source, $destination): Generator {
            self::validatePath($source);
            self::validatePath($destination);

            if (!is_dir($source)) {
                throw new InvalidArgumentException("Source must be a directory: $source");
            }

            $lockFile = self::acquireLock('copy', $source . '->' . $destination);

            try {
                $backupPath = null;
                if (file_exists($destination)) {
                    $backupPath = self::createBackup($destination);
                }

                if (!is_dir($destination)) {
                    $created = @mkdir($destination, 0755, true);
                    yield $created;

                    if (!$created || !is_dir($destination)) {
                        throw new RuntimeException("Failed to create destination directory: $destination");
                    }
                }

                $copiedFiles = [];
                $dir = new DirectoryIterator($source);

                foreach ($dir as $fileinfo) {
                    if ($fileinfo->isDot()) {
                        continue;
                    }

                    $sourcePath = $fileinfo->getPathname();
                    $destinationPath = $destination . DIRECTORY_SEPARATOR . $fileinfo->getFilename();

                    try {
                        if ($fileinfo->isDir()) {
                            yield from self::copy($sourcePath, $destinationPath)->unwrap();
                            $copiedFiles[] = $destinationPath;
                        } else {
                            $tempFile = self::createTempFile($destinationPath);
                            $copied = @copy($sourcePath, $tempFile);
                            yield $copied;

                            if (!$copied || !file_exists($tempFile)) {
                                throw new RuntimeException("Failed to copy file: $sourcePath");
                            }

                            // Verify file integrity
                            if (filesize($sourcePath) !== filesize($tempFile)) {
                                @unlink($tempFile);
                                throw new RuntimeException("File integrity check failed: $sourcePath");
                            }

                            // Atomic move
                            if (!@rename($tempFile, $destinationPath)) {
                                @unlink($tempFile);
                                throw new RuntimeException("Failed to finalize copy: $sourcePath");
                            }

                            $copiedFiles[] = $destinationPath;
                        }
                    } catch (Exception $e) {
                        foreach ($copiedFiles as $copiedFile) {
                            if (is_dir($copiedFile)) {
                                self::recursiveDelete($copiedFile);
                            } else {
                                @unlink($copiedFile);
                            }
                        }

                        if ($backupPath) {
                            self::restoreFromBackup($destination, $backupPath);
                        }

                        throw $e;
                    }
                }

                if ($backupPath) {
                    self::recursiveDelete($backupPath);
                }
            } finally {
                self::releaseLock($lockFile);
            }
        };

        return VOsaka::spawn($fn());
    }

    /**
     * Deletes a directory and its contents.
     * @param string $path The path to the directory to delete.
     * @return Result<bool> Returns true if the deletion was successful, false otherwise.
     * @throws InvalidArgumentException If the path is invalid or not a directory.
     * @throws RuntimeException If the deletion operation fails.
     */
    public static function delete(string $path): Result
    {
        $fn = function () use ($path): Generator {
            self::validatePath($path);

            if (!is_dir($path)) {
                throw new InvalidArgumentException("Path must be a directory: $path");
            }

            $lockFile = self::acquireLock('delete', $path);

            try {
                $backupPath = self::createBackup($path);

                try {
                    $dir = new DirectoryIterator($path);
                    foreach ($dir as $fileinfo) {
                        if ($fileinfo->isDot()) {
                            continue;
                        }

                        $filePath = $fileinfo->getPathname();
                        if ($fileinfo->isDir()) {
                            yield from self::delete($filePath);
                            yield ['type' => 'dir', 'path' => $filePath];
                        } else {
                            $deleted = @unlink($filePath);
                            yield $deleted;

                            if ($deleted) {
                                yield ['type' => 'file', 'path' => $filePath];
                            }
                        }
                    }

                    $dirDeleted = @rmdir($path);
                    yield $dirDeleted;

                    if ($dirDeleted) {
                        yield ['type' => 'dir', 'path' => $path];
                    }

                    if ($backupPath) {
                        self::recursiveDelete($backupPath);
                    }

                } catch (Exception $e) {
                    if ($backupPath) {
                        self::restoreFromBackup($path, $backupPath);
                    }
                    throw $e;
                }

            } finally {
                self::releaseLock($lockFile);
            }
        };

        return VOsaka::spawn($fn());
    }

    /**
     * Moves a directory from source to destination.
     *
     * @param string $source The source directory path.
     * @param string $destination The destination directory path.
     * @return Result<bool> Returns true if the move was successful, false otherwise.
     * @throws InvalidArgumentException If the source or destination paths are invalid.
     * @throws RuntimeException If the move operation fails.
     */
    public static function move(string $source, string $destination): Result
    {
        $fn = function () use ($source, $destination): Generator {
            self::validatePath($source);
            self::validatePath($destination);

            if (!is_dir($source)) {
                throw new InvalidArgumentException("Source must be a directory: $source");
            }

            $lockFile = self::acquireLock('move', $source . '->' . $destination);

            try {
                if (@rename($source, $destination)) {
                    return yield true;
                }

                yield from self::copy($source, $destination)->unwrap();
                yield from self::delete($source);
            } finally {
                self::releaseLock($lockFile);
            }
        };

        return VOsaka::spawn($fn());
    }

    public static function exists(string $path): bool
    {
        return is_dir($path);
    }

    /**
     * Creates a new directory at the specified path with the given permissions.
     *
     * @param string $path The path to create the directory at.
     * @param int $permissions The permissions to set for the new directory (default is 0755).
     * @return Result<bool> Returns true if the directory was created successfully, false otherwise.
     * @throws InvalidArgumentException If the path is invalid.
     * @throws RuntimeException If the directory cannot be created.
     */
    public static function create(string $path, int $permissions = 0755): Result
    {
        $fn = function () use ($path, $permissions): Generator {
            self::validatePath($path);

            if (is_dir($path)) {
                return yield true;
            }

            $created = @mkdir($path, $permissions, true);
            yield $created;

            if (!$created) {
                throw new RuntimeException("Failed to create directory: $path");
            }

            return $created;
        };

        return VOsaka::spawn($fn());
    }

    /**
     * Returns the size of a directory in bytes.
     * @param string $path The path to the directory.
     * @return Result<int> Returns the size of the directory in bytes.
     */
    public static function size(string $path): Result
    {
        $fn = function () use ($path): Generator {
            if (!is_dir($path)) {
                return yield 0;
            }

            $size = 0;
            $dir = new DirectoryIterator($path);

            foreach ($dir as $fileinfo) {
                if ($fileinfo->isDot()) {
                    continue;
                }

                if ($fileinfo->isDir()) {
                    $size += self::size($fileinfo->getPathname());
                } else {
                    $size += $fileinfo->getSize();
                }

                yield;
            }

            return $size;
        };

        return VOsaka::spawn($fn());
    }

    /**
     * Lists the contents of a directory.
     * @param string $path The path to the directory.
     * @param bool $recursive Whether to list contents recursively (default is false).
     * @return Result<array> Returns an array of items in the directory.
     */
    public static function list(string $path, bool $recursive = false): Result
    {
        $fn = function () use ($path, $recursive): Generator {
            if (!is_dir($path)) {
                return yield [];
            }

            $items = [];
            $dir = new DirectoryIterator($path);

            foreach ($dir as $fileinfo) {
                if ($fileinfo->isDot()) {
                    continue;
                }

                $itemPath = $fileinfo->getPathname();
                $items[] = [
                    'name' => $fileinfo->getFilename(),
                    'path' => $itemPath,
                    'type' => $fileinfo->isDir() ? 'directory' : 'file',
                    'size' => $fileinfo->isDir() ? self::size($itemPath) : $fileinfo->getSize(),
                    'modified' => $fileinfo->getMTime(),
                ];

                if ($recursive && $fileinfo->isDir()) {
                    $items = array_merge($items, yield from self::list($itemPath, true)->unwrap());
                }

                yield;
            }

            return $items;
        };

        return VOsaka::spawn($fn());
    }

    /**
     * Cleans up temporary files and locks created by Folder operations.
     * This method should be called during graceful shutdown.
     */
    public static function cleanup(): void
    {
        VOsaka::getLoop()->getGracefulShutDown()->cleanup();
    }

    /**
     * Forcefully cleans up all temporary files and locks, ignoring graceful shutdown state.
     * This should be used in emergency situations where graceful shutdown is not possible.
     */
    public static function forceCleanup(): void
    {
        VOsaka::getLoop()->getGracefulShutDown()->cleanupAll();
    }

    /**
     * Final cleanup method to be called on script termination.
     * It releases all locks and cleans up temporary files.
     */
    public static function finalCleanup(): void
    {
        try {
            foreach (self::$operationLocks as $lockFile => $handle) {
                if (is_resource($handle)) {
                    flock($handle, LOCK_UN);
                    fclose($handle);
                }
                @unlink($lockFile);
            }
            self::$operationLocks = [];

            VOsaka::getLoop()->getGracefulShutDown()->cleanupAll();
        } catch (Exception $e) {
            error_log("Final cleanup failed: " . $e->getMessage());
        }
    }
}