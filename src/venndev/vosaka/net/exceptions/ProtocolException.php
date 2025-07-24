<?php

declare(strict_types=1);

namespace venndev\vosaka\net\exceptions;

/**
 * Exception thrown for protocol-specific errors
 */
class ProtocolException extends NetworkException
{
    private string $protocol;

    public function __construct(string $protocol, string $message)
    {
        $this->protocol = $protocol;
        parent::__construct("[{$protocol}] {$message}");
    }

    public function getProtocol(): string
    {
        return $this->protocol;
    }
}
