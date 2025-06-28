<?php

declare(strict_types=1);

namespace venndev\vosaka\net\dns\exceptions;

use Exception;
use Throwable;

/**
 * Base DNS Exception
 *
 * This is the base exception class for all DNS-related errors.
 * It provides common functionality and serves as the parent class
 * for all specific DNS exception types.
 */
class DNSException extends Exception
{
    /**
     * DNS query that caused the exception
     * @var array{hostname: string, type: string, server?: string}|null
     */
    protected ?array $query = null;

    /**
     * DNS response data (if available)
     * @var string|null
     */
    protected ?string $responseData = null;

    /**
     * DNS error code (if applicable)
     * @var int|null
     */
    protected ?int $dnsErrorCode = null;

    /**
     * Create DNS exception
     *
     * @param string $message Exception message
     * @param int $code Exception code
     * @param Throwable|null $previous Previous exception
     * @param array{hostname: string, type: string, server?: string}|null $query DNS query that caused the exception
     * @param string|null $responseData DNS response data (if available)
     * @param int|null $dnsErrorCode DNS-specific error code
     */
    public function __construct(
        string $message = "",
        int $code = 0,
        ?Throwable $previous = null,
        ?array $query = null,
        ?string $responseData = null,
        ?int $dnsErrorCode = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->query = $query;
        $this->responseData = $responseData;
        $this->dnsErrorCode = $dnsErrorCode;
    }

    /**
     * Get the DNS query that caused this exception
     *
     * @return array{hostname: string, type: string, server?: string}|null
     */
    public function getQuery(): ?array
    {
        return $this->query;
    }

    /**
     * Get the DNS response data (if available)
     *
     * @return string|null
     */
    public function getResponseData(): ?string
    {
        return $this->responseData;
    }

    /**
     * Get the DNS-specific error code
     *
     * @return int|null
     */
    public function getDNSErrorCode(): ?int
    {
        return $this->dnsErrorCode;
    }

    /**
     * Get human-readable DNS error code description
     *
     * @return string|null
     */
    public function getDNSErrorDescription(): ?string
    {
        if ($this->dnsErrorCode === null) {
            return null;
        }

        return match ($this->dnsErrorCode) {
            0 => "No error",
            1
                => "Format error - The name server was unable to interpret the query",
            2
                => "Server failure - The name server was unable to process this query due to a problem with the name server",
            3
                => "Name error - The domain name referenced in the query does not exist",
            4
                => "Not implemented - The name server does not support the requested kind of query",
            5
                => "Refused - The name server refuses to perform the specified operation for policy reasons",
            6 => "YX Domain - Name exists when it should not",
            7 => "YX RR Set - RR Set exists when it should not",
            8 => "NX RR Set - RR Set that should exist does not",
            9 => "Not Auth - Server not authoritative for zone",
            10 => "Not Zone - Name not contained in zone",
            default => "Unknown DNS error code: {$this->dnsErrorCode}",
        };
    }

    /**
     * Get detailed exception information as array
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            "message" => $this->getMessage(),
            "code" => $this->getCode(),
            "file" => $this->getFile(),
            "line" => $this->getLine(),
            "query" => $this->query,
            "dns_error_code" => $this->dnsErrorCode,
            "dns_error_description" => $this->getDNSErrorDescription(),
            "response_data_length" => $this->responseData
                ? strlen($this->responseData)
                : null,
            "trace" => $this->getTraceAsString(),
        ];
    }
}
