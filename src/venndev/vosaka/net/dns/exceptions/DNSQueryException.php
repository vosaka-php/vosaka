<?php

declare(strict_types=1);

namespace venndev\vosaka\net\dns\exceptions;

use Throwable;

/**
 * DNS Query Exception
 *
 * Thrown when a DNS query fails due to network issues,
 * timeout, or other query-related problems.
 */
class DNSQueryException extends DNSException
{
    /**
     * Create DNS query exception
     *
     * @param string $message Exception message
     * @param array{hostname: string, type: string, server?: string}|null $query DNS query that failed
     * @param int $code Exception code
     * @param Throwable|null $previous Previous exception
     */
    public function __construct(
        string $message,
        ?array $query = null,
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous, $query);
    }
}
