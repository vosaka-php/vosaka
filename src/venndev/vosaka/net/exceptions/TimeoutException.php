<?php

declare(strict_types=1);

namespace venndev\vosaka\net\exceptions;

/**
 * Exception thrown when a timeout occurs
 */
class TimeoutException extends NetworkException
{
    private float $timeout;

    public function __construct(float $timeout, string $operation = "")
    {
        $this->timeout = $timeout;
        $message = $operation
            ? "Operation '{$operation}' timed out after {$timeout} seconds"
            : "Operation timed out after {$timeout} seconds";

        parent::__construct($message);
    }

    public function getTimeout(): float
    {
        return $this->timeout;
    }
}
