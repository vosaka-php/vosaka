<?php

declare(strict_types=1);

namespace venndev\vosaka\fs\exceptions;

use Exception;

/**
 * Exception thrown when a file path is invalid or malformed.
 *
 * This exception is thrown when attempting to use file paths that are
 * invalid, malformed, contain illegal characters, or point to restricted
 * system locations that should not be accessed.
 */
class InvalidPathException extends FileSystemException
{
    /**
     * The type of path validation that failed.
     */
    protected string $validationType;

    /**
     * The expected path format or pattern.
     */
    protected ?string $expectedFormat;

    /**
     * InvalidPathException constructor.
     *
     * @param string $path The invalid path
     * @param string $validationType The type of validation that failed
     * @param string $operation The operation being performed
     * @param string|null $expectedFormat The expected path format
     * @param array $context Additional context information
     * @param int $code The exception code
     * @param Exception|null $previous The previous exception
     */
    public function __construct(
        string $path,
        string $validationType,
        string $operation = "path validation",
        ?string $expectedFormat = null,
        array $context = [],
        int $code = 0,
        ?Exception $previous = null
    ) {
        $message = "Invalid path: {$path} (validation type: {$validationType})";

        if ($expectedFormat !== null) {
            $message .= " (expected format: {$expectedFormat})";
        }

        $this->validationType = $validationType;
        $this->expectedFormat = $expectedFormat;

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
     * Get the type of path validation that failed.
     *
     * @return string The validation type
     */
    public function getValidationType(): string
    {
        return $this->validationType;
    }

    /**
     * Get the expected path format.
     *
     * @return string|null The expected format
     */
    public function getExpectedFormat(): ?string
    {
        return $this->expectedFormat;
    }

    /**
     * Create an InvalidPathException for empty paths.
     *
     * @param string $operation The operation being performed
     * @param array $context Additional context
     * @return static
     */
    public static function forEmptyPath(
        string $operation = "access",
        array $context = []
    ): static {
        return new static(
            "",
            "empty path",
            $operation,
            "non-empty string",
            $context
        );
    }

    /**
     * Create an InvalidPathException for null byte attacks.
     *
     * @param string $path The path containing null bytes
     * @param string $operation The operation being performed
     * @param array $context Additional context
     * @return static
     */
    public static function forNullByte(
        string $path,
        string $operation = "access",
        array $context = []
    ): static {
        return new static(
            $path,
            "null byte detected",
            $operation,
            "path without null bytes",
            $context
        );
    }

    /**
     * Create an InvalidPathException for paths that are too long.
     *
     * @param string $path The path that is too long
     * @param int $maxLength The maximum allowed length
     * @param string $operation The operation being performed
     * @param array $context Additional context
     * @return static
     */
    public static function forTooLong(
        string $path,
        int $maxLength,
        string $operation = "access",
        array $context = []
    ): static {
        $context["max_length"] = $maxLength;
        $context["actual_length"] = strlen($path);
        return new static(
            $path,
            "path too long",
            $operation,
            "path with max length {$maxLength}",
            $context
        );
    }

    /**
     * Create an InvalidPathException for illegal characters.
     *
     * @param string $path The path with illegal characters
     * @param array $illegalChars The illegal characters found
     * @param string $operation The operation being performed
     * @param array $context Additional context
     * @return static
     */
    public static function forIllegalCharacters(
        string $path,
        array $illegalChars,
        string $operation = "access",
        array $context = []
    ): static {
        $context["illegal_characters"] = $illegalChars;
        $illegalStr = implode(
            ", ",
            array_map(fn($c) => "'{$c}'", $illegalChars)
        );
        return new static(
            $path,
            "illegal characters",
            $operation,
            "path without characters: {$illegalStr}",
            $context
        );
    }

    /**
     * Create an InvalidPathException for restricted system paths.
     *
     * @param string $path The restricted path
     * @param array $restrictedPaths List of restricted path patterns
     * @param string $operation The operation being performed
     * @param array $context Additional context
     * @return static
     */
    public static function forRestrictedPath(
        string $path,
        array $restrictedPaths = [],
        string $operation = "access",
        array $context = []
    ): static {
        $context["restricted_paths"] = $restrictedPaths;
        return new static(
            $path,
            "restricted system path",
            $operation,
            "non-system path",
            $context
        );
    }

    /**
     * Create an InvalidPathException for paths outside allowed directories.
     *
     * @param string $path The path outside allowed directories
     * @param array $allowedDirectories List of allowed directory patterns
     * @param string $operation The operation being performed
     * @param array $context Additional context
     * @return static
     */
    public static function forOutsideAllowedDirectory(
        string $path,
        array $allowedDirectories,
        string $operation = "access",
        array $context = []
    ): static {
        $context["allowed_directories"] = $allowedDirectories;
        $allowedStr = implode(", ", $allowedDirectories);
        return new static(
            $path,
            "outside allowed directory",
            $operation,
            "path within: {$allowedStr}",
            $context
        );
    }

    /**
     * Create an InvalidPathException for path traversal attempts.
     *
     * @param string $path The path with traversal attempts
     * @param string $operation The operation being performed
     * @param array $context Additional context
     * @return static
     */
    public static function forPathTraversal(
        string $path,
        string $operation = "access",
        array $context = []
    ): static {
        return new static(
            $path,
            "path traversal detected",
            $operation,
            "path without ../ or ..\\ patterns",
            $context
        );
    }

    /**
     * Create an InvalidPathException for malformed paths.
     *
     * @param string $path The malformed path
     * @param string $reason The reason why the path is malformed
     * @param string $operation The operation being performed
     * @param array $context Additional context
     * @return static
     */
    public static function forMalformedPath(
        string $path,
        string $reason,
        string $operation = "access",
        array $context = []
    ): static {
        $context["malform_reason"] = $reason;
        return new static(
            $path,
            "malformed path",
            $operation,
            "well-formed path",
            $context
        );
    }

    /**
     * Create an InvalidPathException for unsupported path types.
     *
     * @param string $path The unsupported path
     * @param string $pathType The detected path type
     * @param array $supportedTypes List of supported path types
     * @param string $operation The operation being performed
     * @param array $context Additional context
     * @return static
     */
    public static function forUnsupportedType(
        string $path,
        string $pathType,
        array $supportedTypes,
        string $operation = "access",
        array $context = []
    ): static {
        $context["detected_type"] = $pathType;
        $context["supported_types"] = $supportedTypes;
        $supportedStr = implode(", ", $supportedTypes);
        return new static(
            $path,
            "unsupported path type",
            $operation,
            "path type: {$supportedStr}",
            $context
        );
    }

    /**
     * Create an InvalidPathException with custom message.
     *
     * @param string $message Custom error message
     * @param string $path The invalid path
     * @param string $validationType The validation type
     * @param string $operation The operation being performed
     * @param array $context Additional context
     * @return static
     */
    public static function withMessage(
        string $message,
        string $path,
        string $validationType,
        string $operation = "path validation",
        array $context = []
    ): static {
        $exception = new static(
            $path,
            $validationType,
            $operation,
            null,
            $context
        );
        $exception->message = $message;
        return $exception;
    }

    /**
     * Create an InvalidPathException for a general path with custom reason.
     *
     * @param string $path The invalid path
     * @param string $reason The reason why the path is invalid
     * @param array $context Additional context
     * @return static
     */
    public static function forPath(
        string $path,
        string $reason,
        array $context = []
    ): static {
        $exception = new static(
            $path,
            "invalid path",
            "path validation",
            null,
            $context
        );
        $exception->message = $reason;
        return $exception;
    }
}
