<?php

declare(strict_types=1);

namespace venndev\vosaka\fs\exceptions;

use Exception;

/**
 * Exception thrown when file input/output operations fail.
 *
 * This exception is thrown when file I/O operations such as reading, writing,
 * copying, or moving files fail due to system-level issues, disk problems,
 * or other I/O related errors.
 */
class FileIOException extends FileSystemException
{
    /**
     * The type of I/O operation that failed.
     */
    protected string $ioOperation;

    /**
     * The number of bytes involved in the operation (if applicable).
     */
    protected ?int $bytesInvolved;

    /**
     * System error code (if available).
     */
    protected ?int $systemErrorCode;

    /**
     * FileIOException constructor.
     *
     * @param string $path The path where the I/O operation failed
     * @param string $ioOperation The specific I/O operation that failed
     * @param string $operation The higher-level operation being performed
     * @param int|null $bytesInvolved Number of bytes involved in the operation
     * @param int|null $systemErrorCode System error code
     * @param array $context Additional context information
     * @param int $code The exception code
     * @param Exception|null $previous The previous exception
     */
    public function __construct(
        string $path,
        string $ioOperation,
        string $operation = "file operation",
        ?int $bytesInvolved = null,
        ?int $systemErrorCode = null,
        array $context = [],
        int $code = 0,
        ?Exception $previous = null
    ) {
        $message = "I/O operation '{$ioOperation}' failed for {$path}";

        if ($bytesInvolved !== null) {
            $message .= " (bytes: {$bytesInvolved})";
        }

        if ($systemErrorCode !== null) {
            $message .= " (system error: {$systemErrorCode})";
        }

        $this->ioOperation = $ioOperation;
        $this->bytesInvolved = $bytesInvolved;
        $this->systemErrorCode = $systemErrorCode;

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
     * Get the I/O operation that failed.
     *
     * @return string The I/O operation
     */
    public function getIOOperation(): string
    {
        return $this->ioOperation;
    }

    /**
     * Get the number of bytes involved in the operation.
     *
     * @return int|null The number of bytes
     */
    public function getBytesInvolved(): ?int
    {
        return $this->bytesInvolved;
    }

    /**
     * Get the system error code.
     *
     * @return int|null The system error code
     */
    public function getSystemErrorCode(): ?int
    {
        return $this->systemErrorCode;
    }

    /**
     * Create a FileIOException for read operations.
     *
     * @param string $path The file path
     * @param int|null $bytesAttempted Number of bytes attempted to read
     * @param array $context Additional context
     * @return static
     */
    public static function forRead(
        string $path,
        ?int $bytesAttempted = null,
        array $context = []
    ): static {
        return new static(
            $path,
            "read",
            "read file",
            $bytesAttempted,
            null,
            $context
        );
    }

    /**
     * Create a FileIOException for write operations.
     *
     * @param string $path The file path
     * @param int|null $bytesAttempted Number of bytes attempted to write
     * @param array $context Additional context
     * @return static
     */
    public static function forWrite(
        string $path,
        ?int $bytesAttempted = null,
        array $context = []
    ): static {
        return new static(
            $path,
            "write",
            "write file",
            $bytesAttempted,
            null,
            $context
        );
    }

    /**
     * Create a FileIOException for copy operations.
     *
     * @param string $sourcePath The source file path
     * @param string $destinationPath The destination file path
     * @param array $context Additional context
     * @return static
     */
    public static function forCopy(
        string $sourcePath,
        string $destinationPath,
        array $context = []
    ): static {
        $context["destination"] = $destinationPath;
        return new static(
            $sourcePath,
            "copy",
            "copy file",
            null,
            null,
            $context
        );
    }

    /**
     * Create a FileIOException for move/rename operations.
     *
     * @param string $sourcePath The source file path
     * @param string $destinationPath The destination file path
     * @param array $context Additional context
     * @return static
     */
    public static function forMove(
        string $sourcePath,
        string $destinationPath,
        array $context = []
    ): static {
        $context["destination"] = $destinationPath;
        return new static(
            $sourcePath,
            "move",
            "move file",
            null,
            null,
            $context
        );
    }

    /**
     * Create a FileIOException for delete operations.
     *
     * @param string $path The file path
     * @param array $context Additional context
     * @return static
     */
    public static function forDelete(string $path, array $context = []): static
    {
        return new static($path, "delete", "delete file", null, null, $context);
    }

    /**
     * Create a FileIOException for file open operations.
     *
     * @param string $path The file path
     * @param string $mode The file open mode
     * @param array $context Additional context
     * @return static
     */
    public static function forOpen(
        string $path,
        string $mode = "r",
        array $context = []
    ): static {
        $context["mode"] = $mode;
        return new static($path, "open", "open file", null, null, $context);
    }

    /**
     * Create a FileIOException for file close operations.
     *
     * @param string $path The file path
     * @param array $context Additional context
     * @return static
     */
    public static function forClose(string $path, array $context = []): static
    {
        return new static($path, "close", "close file", null, null, $context);
    }

    /**
     * Create a FileIOException for flush operations.
     *
     * @param string $path The file path
     * @param array $context Additional context
     * @return static
     */
    public static function forFlush(string $path, array $context = []): static
    {
        return new static($path, "flush", "flush file", null, null, $context);
    }

    /**
     * Create a FileIOException for sync operations.
     *
     * @param string $path The file path
     * @param array $context Additional context
     * @return static
     */
    public static function forSync(string $path, array $context = []): static
    {
        return new static($path, "sync", "sync file", null, null, $context);
    }

    /**
     * Create a FileIOException with system error information.
     *
     * @param string $path The file path
     * @param string $ioOperation The I/O operation
     * @param string $operation The higher-level operation
     * @param int $systemErrorCode System error code
     * @param array $context Additional context
     * @return static
     */
    public static function withSystemError(
        string $path,
        string $ioOperation,
        string $operation,
        int $systemErrorCode,
        array $context = []
    ): static {
        return new static(
            $path,
            $ioOperation,
            $operation,
            null,
            $systemErrorCode,
            $context
        );
    }

    /**
     * Create a FileIOException with custom message.
     *
     * @param string $message Custom error message
     * @param string $path The file path
     * @param string $ioOperation The I/O operation
     * @param string $operation The higher-level operation
     * @param array $context Additional context
     * @return static
     */
    public static function withMessage(
        string $message,
        string $path,
        string $ioOperation,
        string $operation = "file operation",
        array $context = []
    ): static {
        $exception = new static(
            $path,
            $ioOperation,
            $operation,
            null,
            null,
            $context
        );
        $exception->message = $message;
        return $exception;
    }
}
