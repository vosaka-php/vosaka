<?php

declare(strict_types=1);

namespace venndev\vosaka\net\dns\model;

final class AddressRecord
{
    public function __construct(
        public bool $success,
        public ?string $error,
        public string $address
    ) {}

    /**
     * Check if the record parsing was successful
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * Get error message if parsing failed
     */
    public function getError(): ?string
    {
        return $this->error;
    }

    /**
     * Get the IP address
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * Check if this is an IPv4 address
     */
    public function isIPv4(): bool
    {
        return filter_var($this->address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
    }

    /**
     * Check if this is an IPv6 address
     */
    public function isIPv6(): bool
    {
        return filter_var($this->address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
    }

    /**
     * Convert to array format for backwards compatibility
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'error' => $this->error,
            'address' => $this->address,
        ];
    }

    /**
     * Create from array data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['success'] ?? false,
            $data['error'] ?? null,
            $data['address'] ?? ''
        );
    }
}
