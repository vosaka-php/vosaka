<?php

declare(strict_types=1);

namespace venndev\vosaka\net\unix;

use venndev\vosaka\net\contracts\AddressInterface;
use venndev\vosaka\net\exceptions\NetworkException;

/**
 * Unix Socket Address implementation
 */
class UnixAddress implements AddressInterface
{
    private string $path;
    private bool $abstract;

    public function __construct(string $path, bool $abstract = false)
    {
        $this->path = $path;
        $this->abstract = $abstract;
    }

    public function getHost(): string
    {
        if ($this->abstract) {
            return $this->path; // Abstract sockets use the path as host
        }

        // For non-abstract sockets, return the path without leading slash
        return ltrim($this->path, '/');
    }

    public function getPort(): int
    {
        return 0; // Unix sockets do not have a port
    }

    public function toString(): string
    {
        return $this->abstract ? "\0" . $this->path : $this->path;
    }

    public function getFamily(): int
    {
        return AF_UNIX;
    }

    public function isLoopback(): bool
    {
        return true; // Unix sockets are always local
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function isAbstract(): bool
    {
        return $this->abstract;
    }

    /**
     * Create a UnixAddress from a string representation
     * If the address starts with a null byte, it's an abstract socket
     *
     * @param string $address
     * @return static
     */
    public static function parse(string $address): static
    {
        if (str_starts_with($address, "\0")) {
            return new static(substr($address, 1), true);
        }

        return new static($address, false);
    }

    /**
     * Validate Unix socket path
     */
    public static function validate(string $path): void
    {
        if (empty($path)) {
            throw new NetworkException("Unix socket path cannot be empty");
        }

        // Max path length for Unix sockets
        if (strlen($path) > 108) {
            throw new NetworkException("Unix socket path too long (max 108 characters)");
        }

        // Check if abstract socket
        if (str_starts_with($path, "\0")) {
            return; // Abstract sockets don't need file system checks
        }

        $dir = dirname($path);
        if (!is_dir($dir)) {
            throw new NetworkException("Directory does not exist: {$dir}");
        }

        if (!is_writable($dir)) {
            throw new NetworkException("Directory is not writable: {$dir}");
        }
    }
}
