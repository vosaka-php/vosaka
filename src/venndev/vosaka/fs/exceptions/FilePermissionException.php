<?php

declare(strict_types=1);

namespace venndev\vosaka\fs\exceptions;

use Exception;

/**
 * Exception thrown when a file system operation fails due to permission issues.
 *
 * This exception is thrown when attempting to perform operations that require
 * specific permissions (read, write, execute) on files or directories.
 */
class FilePermissionException extends FileSystemException
{
    /**
     * The required permission that was missing.
     */
    protected string $requiredPermission;

    /**
     * The current permissions of the file/directory.
     */
    protected ?string $currentPermissions;

    /**
     * FilePermissionException constructor.
     *
     * @param string $path The path with permission issues
     * @param string $requiredPermission The required permission (read, write, execute)
     * @param string $operation The operation being performed
     * @param string|null $currentPermissions The current permissions (octal format)
     * @param array $context Additional context information
     * @param int $code The exception code
     * @param Exception|null $previous The previous exception
     */
    public function __construct(
        string $path,
        string $requiredPermission,
        string $operation = "access",
        ?string $currentPermissions = null,
        array $context = [],
        int $code = 0,
        ?Exception $previous = null
    ) {
        $message = "Permission denied: {$requiredPermission} access required for {$path}";
        if ($currentPermissions !== null) {
            $message .= " (current permissions: {$currentPermissions})";
        }

        $this->requiredPermission = $requiredPermission;
        $this->currentPermissions = $currentPermissions;

        parent::__construct(
            $message,
            $path,
            $operation,
            $context,
            $code,
            $previous
        );
    }

    /**
     * Get the required permission that was missing.
     *
     * @return string The required permission
     */
    public function getRequiredPermission(): string
    {
        return $this->requiredPermission;
    }

    /**
     * Get the current permissions of the file/directory.
     *
     * @return string|null The current permissions in octal format
     */
    public function getCurrentPermissions(): ?string
    {
        return $this->currentPermissions;
    }

    /**
     * Create a FilePermissionException for read permission.
     *
     * @param string $path The file path
     * @param string $operation The operation being performed
     * @param array $context Additional context
     * @return static
     */
    public static function forRead(
        string $path,
        string $operation = "read",
        array $context = []
    ): static {
        $currentPerms = file_exists($path)
            ? substr(sprintf("%o", fileperms($path)), -4)
            : null;
        return new static($path, "read", $operation, $currentPerms, $context);
    }

    /**
     * Create a FilePermissionException for write permission.
     *
     * @param string $path The file path
     * @param string $operation The operation being performed
     * @param array $context Additional context
     * @return static
     */
    public static function forWrite(
        string $path,
        string $operation = "write",
        array $context = []
    ): static {
        $currentPerms = file_exists($path)
            ? substr(sprintf("%o", fileperms($path)), -4)
            : null;
        return new static($path, "write", $operation, $currentPerms, $context);
    }

    /**
     * Create a FilePermissionException for execute permission.
     *
     * @param string $path The file path
     * @param string $operation The operation being performed
     * @param array $context Additional context
     * @return static
     */
    public static function forExecute(
        string $path,
        string $operation = "execute",
        array $context = []
    ): static {
        $currentPerms = file_exists($path)
            ? substr(sprintf("%o", fileperms($path)), -4)
            : null;
        return new static(
            $path,
            "execute",
            $operation,
            $currentPerms,
            $context
        );
    }

    /**
     * Create a FilePermissionException for directory creation.
     *
     * @param string $path The directory path
     * @param array $context Additional context
     * @return static
     */
    public static function forDirectoryCreation(
        string $path,
        array $context = []
    ): static {
        $parentDir = dirname($path);
        $currentPerms = file_exists($parentDir)
            ? substr(sprintf("%o", fileperms($parentDir)), -4)
            : null;
        return new static(
            $path,
            "write",
            "create directory",
            $currentPerms,
            $context
        );
    }

    /**
     * Create a FilePermissionException with custom message.
     *
     * @param string $message Custom error message
     * @param string $path The file path
     * @param string $requiredPermission The required permission
     * @param string $operation The operation being performed
     * @param array $context Additional context
     * @return static
     */
    public static function withMessage(
        string $message,
        string $path,
        string $requiredPermission,
        string $operation = "access",
        array $context = []
    ): static {
        $currentPerms = file_exists($path)
            ? substr(sprintf("%o", fileperms($path)), -4)
            : null;
        $exception = new static(
            $path,
            $requiredPermission,
            $operation,
            $currentPerms,
            $context
        );
        $exception->message = $message;
        return $exception;
    }
}
