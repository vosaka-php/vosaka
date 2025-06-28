<?php

declare(strict_types=1);

namespace venndev\vosaka\fs\exceptions;

use Exception;

/**
 * Exception thrown when directory-specific operations fail.
 *
 * This exception is thrown when operations specific to directories
 * fail, such as creating, deleting, listing, or managing directory
 * contents and permissions.
 */
class DirectoryException extends FileSystemException
{
    /**
     * The type of directory operation that failed.
     */
    protected string $directoryOperation;

    /**
     * The directory mode/permissions involved (if applicable).
     */
    protected ?int $directoryMode;

    /**
     * Whether the operation was recursive.
     */
    protected bool $isRecursive;

    /**
     * The number of items involved in the operation (if applicable).
     */
    protected ?int $itemCount;

    /**
     * DirectoryException constructor.
     *
     * @param string $path The directory path where the operation failed
     * @param string $directoryOperation The specific directory operation that failed
     * @param string $operation The higher-level operation being performed
     * @param int|null $directoryMode The directory mode/permissions
     * @param bool $isRecursive Whether the operation was recursive
     * @param int|null $itemCount Number of items involved
     * @param array $context Additional context information
     * @param int $code The exception code
     * @param Exception|null $previous The previous exception
     */
    public function __construct(
        string $path,
        string $directoryOperation,
        string $operation = "directory operation",
        ?int $directoryMode = null,
        bool $isRecursive = false,
        ?int $itemCount = null,
        array $context = [],
        int $code = 0,
        ?Exception $previous = null
    ) {
        $message = "Directory operation '{$directoryOperation}' failed for {$path}";

        if ($directoryMode !== null) {
            $message .= " (mode: " . sprintf("%o", $directoryMode) . ")";
        }

        if ($isRecursive) {
            $message .= " (recursive)";
        }

        if ($itemCount !== null) {
            $message .= " (items: {$itemCount})";
        }

        $this->directoryOperation = $directoryOperation;
        $this->directoryMode = $directoryMode;
        $this->isRecursive = $isRecursive;
        $this->itemCount = $itemCount;

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
     * Get the directory operation that failed.
     *
     * @return string The directory operation
     */
    public function getDirectoryOperation(): string
    {
        return $this->directoryOperation;
    }

    /**
     * Get the directory mode/permissions.
     *
     * @return int|null The directory mode
     */
    public function getDirectoryMode(): ?int
    {
        return $this->directoryMode;
    }

    /**
     * Check if the operation was recursive.
     *
     * @return bool True if recursive
     */
    public function isRecursive(): bool
    {
        return $this->isRecursive;
    }

    /**
     * Get the number of items involved in the operation.
     *
     * @return int|null The item count
     */
    public function getItemCount(): ?int
    {
        return $this->itemCount;
    }

    /**
     * Create a DirectoryException for create operations.
     *
     * @param string $path The directory path
     * @param int $mode The directory permissions
     * @param bool $recursive Whether the operation was recursive
     * @param array $context Additional context
     * @return static
     */
    public static function forCreate(
        string $path,
        int $mode = 0755,
        bool $recursive = false,
        array $context = []
    ): static {
        return new static(
            $path,
            "create",
            "create directory",
            $mode,
            $recursive,
            null,
            $context
        );
    }

    /**
     * Create a DirectoryException for delete operations.
     *
     * @param string $path The directory path
     * @param bool $recursive Whether the operation was recursive
     * @param int|null $itemCount Number of items in the directory
     * @param array $context Additional context
     * @return static
     */
    public static function forDelete(
        string $path,
        bool $recursive = false,
        ?int $itemCount = null,
        array $context = []
    ): static {
        return new static(
            $path,
            "delete",
            "delete directory",
            null,
            $recursive,
            $itemCount,
            $context
        );
    }

    /**
     * Create a DirectoryException for list operations.
     *
     * @param string $path The directory path
     * @param bool $recursive Whether the operation was recursive
     * @param array $context Additional context
     * @return static
     */
    public static function forList(
        string $path,
        bool $recursive = false,
        array $context = []
    ): static {
        return new static(
            $path,
            "list",
            "list directory",
            null,
            $recursive,
            null,
            $context
        );
    }

    /**
     * Create a DirectoryException for copy operations.
     *
     * @param string $sourcePath The source directory path
     * @param string $destinationPath The destination directory path
     * @param bool $recursive Whether the operation was recursive
     * @param int|null $itemCount Number of items to copy
     * @param array $context Additional context
     * @return static
     */
    public static function forCopy(
        string $sourcePath,
        string $destinationPath,
        bool $recursive = true,
        ?int $itemCount = null,
        array $context = []
    ): static {
        $context["destination"] = $destinationPath;
        return new static(
            $sourcePath,
            "copy",
            "copy directory",
            null,
            $recursive,
            $itemCount,
            $context
        );
    }

    /**
     * Create a DirectoryException for move operations.
     *
     * @param string $sourcePath The source directory path
     * @param string $destinationPath The destination directory path
     * @param int|null $itemCount Number of items to move
     * @param array $context Additional context
     * @return static
     */
    public static function forMove(
        string $sourcePath,
        string $destinationPath,
        ?int $itemCount = null,
        array $context = []
    ): static {
        $context["destination"] = $destinationPath;
        return new static(
            $sourcePath,
            "move",
            "move directory",
            null,
            false,
            $itemCount,
            $context
        );
    }

    /**
     * Create a DirectoryException for empty directory operations.
     *
     * @param string $path The directory path
     * @param int|null $itemCount Number of items that prevented the operation
     * @param array $context Additional context
     * @return static
     */
    public static function forEmpty(
        string $path,
        ?int $itemCount = null,
        array $context = []
    ): static {
        $context["non_empty"] = true;
        return new static(
            $path,
            "empty",
            "empty directory",
            null,
            false,
            $itemCount,
            $context
        );
    }

    /**
     * Create a DirectoryException for read operations.
     *
     * @param string $path The directory path
     * @param array $context Additional context
     * @return static
     */
    public static function forRead(string $path, array $context = []): static
    {
        return new static(
            $path,
            "read",
            "read directory",
            null,
            false,
            null,
            $context
        );
    }

    /**
     * Create a DirectoryException for traversal operations.
     *
     * @param string $path The directory path
     * @param bool $recursive Whether the traversal was recursive
     * @param int|null $depthReached The depth reached before failure
     * @param array $context Additional context
     * @return static
     */
    public static function forTraversal(
        string $path,
        bool $recursive = false,
        ?int $depthReached = null,
        array $context = []
    ): static {
        if ($depthReached !== null) {
            $context["depth_reached"] = $depthReached;
        }
        return new static(
            $path,
            "traversal",
            "traverse directory",
            null,
            $recursive,
            null,
            $context
        );
    }

    /**
     * Create a DirectoryException for size calculation operations.
     *
     * @param string $path The directory path
     * @param bool $recursive Whether the calculation was recursive
     * @param int|null $itemsProcessed Number of items processed before failure
     * @param array $context Additional context
     * @return static
     */
    public static function forSize(
        string $path,
        bool $recursive = true,
        ?int $itemsProcessed = null,
        array $context = []
    ): static {
        return new static(
            $path,
            "size calculation",
            "calculate directory size",
            null,
            $recursive,
            $itemsProcessed,
            $context
        );
    }

    /**
     * Create a DirectoryException for not empty situations.
     *
     * @param string $path The directory path
     * @param int $itemCount Number of items in the directory
     * @param string $operation The operation that requires empty directory
     * @param array $context Additional context
     * @return static
     */
    public static function forNotEmpty(
        string $path,
        int $itemCount,
        string $operation = "directory operation",
        array $context = []
    ): static {
        $context["requires_empty"] = true;
        return new static(
            $path,
            "not empty",
            $operation,
            null,
            false,
            $itemCount,
            $context
        );
    }

    /**
     * Create a DirectoryException for already exists situations.
     *
     * @param string $path The directory path
     * @param string $operation The operation being performed
     * @param array $context Additional context
     * @return static
     */
    public static function forAlreadyExists(
        string $path,
        string $operation = "create directory",
        array $context = []
    ): static {
        $context["already_exists"] = true;
        return new static(
            $path,
            "already exists",
            $operation,
            null,
            false,
            null,
            $context
        );
    }

    /**
     * Create a DirectoryException with custom message.
     *
     * @param string $message Custom error message
     * @param string $path The directory path
     * @param string $directoryOperation The directory operation
     * @param string $operation The higher-level operation
     * @param array $context Additional context
     * @return static
     */
    public static function withMessage(
        string $message,
        string $path,
        string $directoryOperation,
        string $operation = "directory operation",
        array $context = []
    ): static {
        $exception = new static(
            $path,
            $directoryOperation,
            $operation,
            null,
            false,
            null,
            $context
        );
        $exception->message = $message;
        return $exception;
    }

    /**
     * Create a DirectoryException for creation operations.
     *
     * @param string $path The directory path
     * @param string $message Additional error message
     * @param array $context Additional context
     * @return static
     */
    public static function forCreation(
        string $path,
        string $message = "",
        array $context = []
    ): static {
        $fullMessage = "Failed to create directory '{$path}'";
        if (!empty($message)) {
            $fullMessage .= ": {$message}";
        }

        $exception = new static(
            $path,
            "creation",
            "create directory",
            null,
            false,
            null,
            $context
        );
        $exception->message = $fullMessage;
        return $exception;
    }

    /**
     * Create a DirectoryException for removal operations.
     *
     * @param string $path The directory path
     * @param string $message Additional error message
     * @param array $context Additional context
     * @return static
     */
    public static function forRemoval(
        string $path,
        string $message = "",
        array $context = []
    ): static {
        $fullMessage = "Failed to remove directory '{$path}'";
        if (!empty($message)) {
            $fullMessage .= ": {$message}";
        }

        $exception = new static(
            $path,
            "removal",
            "remove directory",
            null,
            false,
            null,
            $context
        );
        $exception->message = $fullMessage;
        return $exception;
    }

    /**
     * Create a DirectoryException for not found situations.
     *
     * @param string $path The directory path
     * @param array $context Additional context
     * @return static
     */
    public static function forNotFound(
        string $path,
        array $context = []
    ): static {
        $exception = new static(
            $path,
            "not found",
            "directory operation",
            null,
            false,
            null,
            $context
        );
        $exception->message = "Directory not found: '{$path}'";
        return $exception;
    }

    /**
     * Create a DirectoryException for permission operations.
     *
     * @param string $path The directory path
     * @param string $operation The operation requiring permission
     * @param array $context Additional context
     * @return static
     */
    public static function forPermission(
        string $path,
        string $operation = "access",
        array $context = []
    ): static {
        $exception = new static(
            $path,
            "permission denied",
            $operation,
            null,
            false,
            null,
            $context
        );
        $exception->message = "Permission denied for '{$operation}' operation on directory: '{$path}'";
        return $exception;
    }

    /**
     * Create a DirectoryException for walk operations.
     *
     * @param string $path The directory path
     * @param string $message Additional error message
     * @param array $context Additional context
     * @return static
     */
    public static function forWalk(
        string $path,
        string $message = "",
        array $context = []
    ): static {
        $fullMessage = "Failed to walk directory '{$path}'";
        if (!empty($message)) {
            $fullMessage .= ": {$message}";
        }

        $exception = new static(
            $path,
            "walk",
            "walk directory",
            null,
            true,
            null,
            $context
        );
        $exception->message = $fullMessage;
        return $exception;
    }

    /**
     * Create a DirectoryException for exists situations.
     *
     * @param string $path The directory path
     * @param array $context Additional context
     * @return static
     */
    public static function forExists(string $path, array $context = []): static
    {
        $exception = new static(
            $path,
            "already exists",
            "directory operation",
            null,
            false,
            null,
            $context
        );
        $exception->message = "Directory already exists: '{$path}'";
        return $exception;
    }

    /**
     * Create a DirectoryException for stat operations.
     *
     * @param string $path The directory path
     * @param array $context Additional context
     * @return static
     */
    public static function forStat(string $path, array $context = []): static
    {
        $exception = new static(
            $path,
            "stat",
            "get directory metadata",
            null,
            false,
            null,
            $context
        );
        $exception->message = "Failed to get metadata for directory: '{$path}'";
        return $exception;
    }
}
