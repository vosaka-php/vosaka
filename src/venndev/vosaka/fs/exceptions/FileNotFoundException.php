<?php

declare(strict_types=1);

namespace venndev\vosaka\fs\exceptions;

use Exception;

/**
 * Exception thrown when a file or directory is not found.
 *
 * This exception is thrown when attempting to perform operations on files
 * or directories that do not exist on the file system.
 */
class FileNotFoundException extends FileSystemException
{
    /**
     * FileNotFoundException constructor.
     *
     * @param string $path The path that was not found
     * @param string $operation The operation being performed
     * @param array $context Additional context information
     * @param int $code The exception code
     * @param Exception|null $previous The previous exception
     */
    public function __construct(
        string $path,
        string $operation = "access",
        array $context = [],
        int $code = 0,
        ?Exception $previous = null
    ) {
        $message = "File or directory not found: {$path}";
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
     * Create a FileNotFoundException for a file.
     *
     * @param string $path The file path
     * @param string $operation The operation being performed
     * @param array $context Additional context
     * @return static
     */
    public static function forFile(
        string $path,
        string $operation = "read",
        array $context = []
    ): static {
        $context["type"] = "file";
        return new static($path, $operation, $context);
    }

    /**
     * Create a FileNotFoundException for a directory.
     *
     * @param string $path The directory path
     * @param string $operation The operation being performed
     * @param array $context Additional context
     * @return static
     */
    public static function forDirectory(
        string $path,
        string $operation = "access",
        array $context = []
    ): static {
        $context["type"] = "directory";
        return new static($path, $operation, $context);
    }

    /**
     * Create a FileNotFoundException with a custom message.
     *
     * @param string $message Custom error message
     * @param string $path The path that was not found
     * @param string $operation The operation being performed
     * @param array $context Additional context
     * @return static
     */
    public static function withMessage(
        string $message,
        string $path,
        string $operation = "access",
        array $context = []
    ): static {
        $exception = new static($path, $operation, $context);
        $exception->message = $message;
        return $exception;
    }
}
