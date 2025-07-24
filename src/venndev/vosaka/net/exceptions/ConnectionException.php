<?php

declare(strict_types=1);

namespace venndev\vosaka\net\exceptions;

/**
 * Exception thrown when connection operations fail
 */
class ConnectionException extends NetworkException
{
    private ?string $host = null;
    private ?int $port = null;

    public function setEndpoint(string $host, int $port): self
    {
        $this->host = $host;
        $this->port = $port;
        return $this;
    }

    public function getHost(): ?string
    {
        return $this->host;
    }

    public function getPort(): ?int
    {
        return $this->port;
    }
}
