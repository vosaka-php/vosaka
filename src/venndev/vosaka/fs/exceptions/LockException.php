<?php

declare(strict_types=1);

namespace venndev\vosaka\fs\exceptions;

use Exception;

/**
 * Exception thrown when file locking operations fail.
 *
 * This exception is thrown when attempting to acquire, release, or manage
 * file locks fails due to contention, system limitations, or other
 * locking-related issues.
 */
class LockException extends FileSystemException
{
    /**
     * The type of lock operation that failed.
     */
    protected string $lockOperation;

    /**
     * The lock type (shared, exclusive, etc.).
     */
    protected ?string $lockType;

    /**
     * The process ID that currently holds the lock (if known).
     */
    protected ?int $lockHolderPid;

    /**
     * The timeout value for the lock operation (if applicable).
     */
    protected ?int $timeout;

    /**
     * LockException constructor.
     *
     * @param string $path The path where the lock operation failed
     * @param string $lockOperation The specific lock operation that failed
     * @param string $operation The higher-level operation being performed
     * @param string|null $lockType The type of lock
     * @param int|null $lockHolderPid PID of current lock holder
     * @param int|null $timeout Lock timeout value
     * @param array $context Additional context information
     * @param int $code The exception code
     * @param Exception|null $previous The previous exception
     */
    public function __construct(
        string $path,
        string $lockOperation,
        string $operation = "file operation",
        ?string $lockType = null,
        ?int $lockHolderPid = null,
        ?int $timeout = null,
        array $context = [],
        int $code = 0,
        ?Exception $previous = null
    ) {
        $message = "Lock operation '{$lockOperation}' failed for {$path}";

        if ($lockType !== null) {
            $message .= " (lock type: {$lockType})";
        }

        if ($lockHolderPid !== null) {
            $message .= " (held by PID: {$lockHolderPid})";
        }

        if ($timeout !== null) {
            $message .= " (timeout: {$timeout}s)";
        }

        $this->lockOperation = $lockOperation;
        $this->lockType = $lockType;
        $this->lockHolderPid = $lockHolderPid;
        $this->timeout = $timeout;

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
     * Get the lock operation that failed.
     *
     * @return string The lock operation
     */
    public function getLockOperation(): string
    {
        return $this->lockOperation;
    }

    /**
     * Get the lock type.
     *
     * @return string|null The lock type
     */
    public function getLockType(): ?string
    {
        return $this->lockType;
    }

    /**
     * Get the PID of the process holding the lock.
     *
     * @return int|null The lock holder PID
     */
    public function getLockHolderPid(): ?int
    {
        return $this->lockHolderPid;
    }

    /**
     * Get the timeout value for the lock operation.
     *
     * @return int|null The timeout value
     */
    public function getTimeout(): ?int
    {
        return $this->timeout;
    }

    /**
     * Create a LockException for acquire operations.
     *
     * @param string $path The file path
     * @param string $lockType The lock type (shared, exclusive)
     * @param string $operation The operation being performed
     * @param int|null $timeout Lock timeout
     * @param array $context Additional context
     * @return static
     */
    public static function forAcquire(
        string $path,
        string $lockType = "exclusive",
        string $operation = "acquire lock",
        ?int $timeout = null,
        array $context = []
    ): static {
        return new static(
            $path,
            "acquire",
            $operation,
            $lockType,
            null,
            $timeout,
            $context
        );
    }

    /**
     * Create a LockException for release operations.
     *
     * @param string $path The file path
     * @param string $operation The operation being performed
     * @param array $context Additional context
     * @return static
     */
    public static function forRelease(
        string $path,
        string $operation = "release lock",
        array $context = []
    ): static {
        return new static(
            $path,
            "release",
            $operation,
            null,
            null,
            null,
            $context
        );
    }

    /**
     * Create a LockException for timeout situations.
     *
     * @param string $path The file path
     * @param int|float $timeout The timeout value that was exceeded
     * @param string $lockType The lock type
     * @param string $operation The operation being performed
     * @param array $context Additional context
     * @return static
     */
    public static function forTimeout(
        string $path,
        int|float $timeout,
        string $lockType = "exclusive",
        string $operation = "acquire lock",
        array $context = []
    ): static {
        $context["timeout_exceeded"] = true;
        return new static(
            $path,
            "timeout",
            $operation,
            $lockType,
            null,
            (int) $timeout,
            $context
        );
    }

    /**
     * Create a LockException for contention situations.
     *
     * @param string $path The file path
     * @param int|null $lockHolderPid PID of the process holding the lock
     * @param string $lockType The lock type
     * @param string $operation The operation being performed
     * @param array $context Additional context
     * @return static
     */
    public static function forContention(
        string $path,
        ?int $lockHolderPid = null,
        string $lockType = "exclusive",
        string $operation = "acquire lock",
        array $context = []
    ): static {
        $context["contention"] = true;
        return new static(
            $path,
            "contention",
            $operation,
            $lockType,
            $lockHolderPid,
            null,
            $context
        );
    }

    /**
     * Create a LockException for deadlock situations.
     *
     * @param string $path The file path
     * @param array $involvedPids PIDs involved in the deadlock
     * @param string $operation The operation being performed
     * @param array $context Additional context
     * @return static
     */
    public static function forDeadlock(
        string $path,
        array $involvedPids = [],
        string $operation = "acquire lock",
        array $context = []
    ): static {
        $context["deadlock"] = true;
        $context["involved_pids"] = $involvedPids;
        return new static(
            $path,
            "deadlock",
            $operation,
            "exclusive",
            null,
            null,
            $context
        );
    }

    /**
     * Create a LockException for stale lock situations.
     *
     * @param string $path The file path
     * @param int|null $stalePid PID of the stale lock holder
     * @param string $operation The operation being performed
     * @param array $context Additional context
     * @return static
     */
    public static function forStaleLock(
        string $path,
        ?int $stalePid = null,
        string $operation = "acquire lock",
        array $context = []
    ): static {
        $context["stale_lock"] = true;
        return new static(
            $path,
            "stale lock",
            $operation,
            null,
            $stalePid,
            null,
            $context
        );
    }

    /**
     * Create a LockException for invalid lock operations.
     *
     * @param string $path The file path
     * @param string $reason The reason why the lock operation is invalid
     * @param string $operation The operation being performed
     * @param array $context Additional context
     * @return static
     */
    public static function forInvalidOperation(
        string $path,
        string $reason,
        string $operation = "lock operation",
        array $context = []
    ): static {
        $context["invalid_reason"] = $reason;
        return new static(
            $path,
            "invalid operation",
            $operation,
            null,
            null,
            null,
            $context
        );
    }

    /**
     * Create a LockException with custom message.
     *
     * @param string $message Custom error message
     * @param string $path The file path
     * @param string $lockOperation The lock operation
     * @param string $operation The higher-level operation
     * @param array $context Additional context
     * @return static
     */
    public static function withMessage(
        string $message,
        string $path,
        string $lockOperation,
        string $operation = "file operation",
        array $context = []
    ): static {
        $exception = new static(
            $path,
            $lockOperation,
            $operation,
            null,
            null,
            null,
            $context
        );
        $exception->message = $message;
        return $exception;
    }
}
