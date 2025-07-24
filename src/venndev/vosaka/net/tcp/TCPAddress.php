<?php

declare(strict_types=1);

namespace venndev\vosaka\net\tcp;

use venndev\vosaka\net\contracts\AddressInterface;
use venndev\vosaka\net\exceptions\NetworkException;

class TCPAddress implements AddressInterface
{
    private string $host;
    private int $port;
    private int $family;

    public function __construct(string $host, int $port)
    {
        $this->host = $host;
        $this->port = $port;

        // Determine family
        if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $this->family = AF_INET6;
        } else {
            $this->family = AF_INET;
        }
    }

    /**
     * Convert address to string representation
     *
     * @return string
     */
    public function toString(): string
    {
        if ($this->family === AF_INET6) {
            return "[{$this->host}]:{$this->port}";
        }

        return "{$this->host}:{$this->port}";
    }

    /**
     * Get the address family
     *
     * @return int
     */
    public function getFamily(): int
    {
        return $this->family;
    }

    /**
     * Check if the address is a loopback address
     *
     * @return bool
     */
    public function isLoopback(): bool
    {
        return $this->host === '127.0.0.1' ||
            $this->host === '::1' ||
            $this->host === 'localhost';
    }

    /**
     * Get the host and port
     *
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * Get the port number
     *
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * Parse a string address into a TCPAddress object
     *
     * @param string $address Address in the format host:port or [host]:port for IPv6
     * @return static
     * @throws NetworkException
     */
    public static function parse(string $address): static
    {
        // Handle IPv6 format [::1]:8080
        if (preg_match('/^\[([^\]]+)\]:(\d+)$/', $address, $matches)) {
            return new static($matches[1], (int)$matches[2]);
        }

        // Handle IPv4 format host:port
        $parts = explode(':', $address);
        if (count($parts) !== 2) {
            throw new NetworkException("Invalid address format: {$address}");
        }

        $port = (int) $parts[1];
        if ($port < 1 || $port > 65535) {
            throw new NetworkException("Invalid port: {$port}");
        }

        return new static($parts[0], $port);
    }
}
