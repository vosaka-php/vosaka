<?php

declare(strict_types=1);

namespace venndev\vosaka\net\dns\exceptions;

use Throwable;

/**
 * DNS Network Exception
 *
 * Thrown when network-related errors occur during DNS operations,
 * such as socket creation failures or connection issues.
 */
class DNSNetworkException extends DNSQueryException
{
    /**
     * Network error code
     * @var int|null
     */
    private ?int $networkErrorCode = null;

    /**
     * Create DNS network exception
     *
     * @param string $message Exception message
     * @param int|null $networkErrorCode Network-specific error code
     * @param array{hostname: string, type: string, server?: string}|null $query DNS query that failed
     * @param int $code Exception code
     * @param Throwable|null $previous Previous exception
     */
    public function __construct(
        string $message,
        ?int $networkErrorCode = null,
        ?array $query = null,
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $query, $code, $previous);
        $this->networkErrorCode = $networkErrorCode;
    }

    /**
     * Get the network error code
     *
     * @return int|null
     */
    public function getNetworkErrorCode(): ?int
    {
        return $this->networkErrorCode;
    }
}
