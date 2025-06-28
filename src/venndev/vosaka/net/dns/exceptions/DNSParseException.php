<?php

declare(strict_types=1);

namespace venndev\vosaka\net\dns\exceptions;

use Throwable;

/**
 * DNS Parse Exception
 *
 * Thrown when DNS response parsing fails due to malformed
 * or invalid response data.
 */
class DNSParseException extends DNSException
{
    /**
     * Offset in response where parsing failed
     * @var int|null
     */
    private ?int $parseOffset = null;

    /**
     * Create DNS parse exception
     *
     * @param string $message Exception message
     * @param string|null $responseData DNS response that failed to parse
     * @param int|null $parseOffset Offset where parsing failed
     * @param int $code Exception code
     * @param Throwable|null $previous Previous exception
     */
    public function __construct(
        string $message,
        ?string $responseData = null,
        ?int $parseOffset = null,
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous, null, $responseData);
        $this->parseOffset = $parseOffset;
    }

    /**
     * Get the offset where parsing failed
     *
     * @return int|null
     */
    public function getParseOffset(): ?int
    {
        return $this->parseOffset;
    }
}
