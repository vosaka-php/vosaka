<?php

declare(strict_types=1);

namespace venndev\vosaka\net\contracts;

/**
 * Base interface for network addresses
 */
interface AddressInterface
{
    /**
     * Get string representation of the address
     */
    public function toString(): string;

    /**
     * Get the address family (AF_INET, AF_INET6, AF_UNIX)
     */
    public function getFamily(): int;

    /**
     * Check if this is a loopback address
     */
    public function isLoopback(): bool;

    /**
     * Parse address from string
     */
    public static function parse(string $address): static;

    /**
     * Get the host part of the address
     */
    public function getHost(): string;

    /**
     * Get the port part of the address
     */
    public function getPort(): int;
}
