<?php

declare(strict_types=1);

namespace venndev\vosaka\net\contracts;

use venndev\vosaka\core\Result;

/**
 * Interface for server/listener sockets
 */
interface ServerInterface
{
    /**
     * Accept incoming connection
     * 
     * @param float $timeout Timeout in seconds, 0 for non-blocking
     * @return Result<ConnectionInterface|null>
     */
    public function accept(float $timeout = 0.0): Result;

    /**
     * Close the server
     */
    public function close(): void;

    /**
     * Check if server is closed
     */
    public function isClosed(): bool;

    /**
     * Get server address
     */
    public function getAddress(): AddressInterface;

    /**
     * Get server options
     */
    public function getOptions(): array;
}
