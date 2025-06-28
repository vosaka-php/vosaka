<?php

declare(strict_types=1);

namespace venndev\vosaka\net\dns\exceptions;

use Throwable;

/**
 * DNSSEC Exception
 *
 * Thrown when DNSSEC validation fails or encounters errors.
 */
class DNSSECException extends DNSException
{
    /**
     * DNSSEC validation error type
     * @var string
     */
    private string $validationError;

    /**
     * DNSSEC records involved in the failure
     * @var array
     */
    private array $dnssecRecords;

    /**
     * Create DNSSEC exception
     *
     * @param string $message Exception message
     * @param string $validationError Type of DNSSEC validation error
     * @param array $dnssecRecords DNSSEC records involved
     * @param array{hostname: string, type: string, server?: string}|null $query DNS query that failed validation
     * @param int $code Exception code
     * @param Throwable|null $previous Previous exception
     */
    public function __construct(
        string $message,
        string $validationError,
        array $dnssecRecords = [],
        ?array $query = null,
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous, $query);
        $this->validationError = $validationError;
        $this->dnssecRecords = $dnssecRecords;
    }

    /**
     * Get the DNSSEC validation error type
     *
     * @return string
     */
    public function getValidationError(): string
    {
        return $this->validationError;
    }

    /**
     * Get the DNSSEC records involved in the failure
     *
     * @return array
     */
    public function getDNSsecRecords(): array
    {
        return $this->dnssecRecords;
    }
}
