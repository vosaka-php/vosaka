<?php

declare(strict_types=1);

namespace venndev\vosaka\net\contracts;

use venndev\vosaka\core\Result;

/**
 * Base interface for all network connections
 */
interface ConnectionInterface
{
    /**
     * Read data from the connection
     * 
     * @param int $length Maximum bytes to read, -1 for all available
     * @return Result<string>
     */
    public function read(int $length = -1): Result;

    /**
     * Write data to the connection
     * 
     * @param string $data Data to write
     * @return Result<int> Number of bytes written
     */
    public function write(string $data): Result;

    /**
     * Close the connection
     */
    public function close(): void;

    /**
     * Check if connection is closed
     */
    public function isClosed(): bool;

    /**
     * Get local address
     */
    public function getLocalAddress(): AddressInterface;

    /**
     * Get remote address
     */
    public function getRemoteAddress(): AddressInterface;

    /**
     * Set read timeout in seconds
     */
    public function setReadTimeout(float $seconds): void;

    /**
     * Set write timeout in seconds
     */
    public function setWriteTimeout(float $seconds): void;
}
