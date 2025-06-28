<?php

declare(strict_types=1);

namespace venndev\vosaka\net\dns\exceptions;

use Throwable;

/**
 * DNS Timeout Exception
 *
 * Thrown when a DNS query times out before receiving a response.
 */
class DNSTimeoutException extends DNSQueryException
{
    /**
     * Timeout duration in seconds
     * @var int
     */
    private int $timeoutDuration;

    /**
     * Create DNS timeout exception
     *
     * @param string $message Exception message
     * @param int $timeoutDuration Timeout duration in seconds
     * @param array{hostname: string, type: string, server?: string}|null $query DNS query that timed out
     * @param int $code Exception code
     * @param Throwable|null $previous Previous exception
     */
    public function __construct(
        string $message,
        int $timeoutDuration,
        ?array $query = null,
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $query, $code, $previous);
        $this->timeoutDuration = $timeoutDuration;
    }

    /**
     * Get the timeout duration
     *
     * @return int Timeout duration in seconds
     */
    public function getTimeoutDuration(): int
    {
        return $this->timeoutDuration;
    }
}
