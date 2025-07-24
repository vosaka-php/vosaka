<?php

declare(strict_types=1);

namespace venndev\vosaka\net\exceptions;

/**
 * Exception thrown when bind operations fail
 */
class BindException extends NetworkException
{
    private ?string $address = null;

    public function setAddress(string $address): self
    {
        $this->address = $address;
        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }
}
