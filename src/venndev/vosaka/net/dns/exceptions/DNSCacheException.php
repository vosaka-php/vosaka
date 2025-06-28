<?php

declare(strict_types=1);

namespace venndev\vosaka\net\dns\exceptions;

use Throwable;

/**
 * DNS Cache Exception
 *
 * Thrown when DNS cache operations fail or encounter errors.
 */
class DNSCacheException extends DNSException
{
    /**
     * Cache operation that failed
     * @var string
     */
    private string $cacheOperation;

    /**
     * Cache key involved in the failure
     * @var string|null
     */
    private ?string $cacheKey = null;

    /**
     * Create DNS cache exception
     *
     * @param string $message Exception message
     * @param string $cacheOperation Cache operation that failed (get, set, delete, etc.)
     * @param string|null $cacheKey Cache key involved
     * @param int $code Exception code
     * @param Throwable|null $previous Previous exception
     */
    public function __construct(
        string $message,
        string $cacheOperation,
        ?string $cacheKey = null,
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->cacheOperation = $cacheOperation;
        $this->cacheKey = $cacheKey;
    }

    /**
     * Get the cache operation that failed
     *
     * @return string
     */
    public function getCacheOperation(): string
    {
        return $this->cacheOperation;
    }

    /**
     * Get the cache key involved in the failure
     *
     * @return string|null
     */
    public function getCacheKey(): ?string
    {
        return $this->cacheKey;
    }
}
