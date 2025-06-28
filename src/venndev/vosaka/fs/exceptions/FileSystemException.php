<?php

declare(strict_types=1);

namespace venndev\vosaka\fs\exceptions;

use Exception;

/**
 * Base exception class for all file system related exceptions.
 *
 * This class serves as the parent for all file system exceptions in the VOsaka framework.
 * It provides common functionality and allows for easy exception handling by catching
 * this base class to handle all file system related errors.
 */
class FileSystemException extends Exception
{
    /**
     * The path that caused the exception.
     */
    protected string $path;

    /**
     * The operation that was being performed when the exception occurred.
     */
    protected string $operation;

    /**
     * Additional context information about the exception.
     */
    protected array $context;

    /**
     * FileSystemException constructor.
     *
     * @param string $message The exception message
     * @param string $path The path that caused the exception
     * @param string $operation The operation being performed
     * @param array $context Additional context information
     * @param int $code The exception code
     * @param Exception|null $previous The previous exception
     */
    public function __construct(
        string $message,
        string $path = '',
        string $operation = '',
        array $context = [],
        int $code = 0,
        ?Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->path = $path;
        $this->operation = $operation;
        $this->context = $context;
    }

    /**
     * Get the path that caused the exception.
     *
     * @return string The path
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Get the operation that was being performed.
     *
     * @return string The operation
     */
    public function getOperation(): string
    {
        return $this->operation;
    }

    /**
     * Get additional context information.
     *
     * @return array The context
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Get a formatted string representation of the exception.
     *
     * @return string The formatted exception string
     */
    public function getFormattedMessage(): string
    {
        $message = $this->getMessage();

        if (!empty($this->operation)) {
            $message = "Operation '{$this->operation}': {$message}";
        }

        if (!empty($this->path)) {
            $message .= " (Path: {$this->path})";
        }

        if (!empty($this->context)) {
            $contextStr = json_encode($this->context, JSON_PRETTY_PRINT);
            $message .= " (Context: {$contextStr})";
        }

        return $message;
    }
}
