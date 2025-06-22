<?php

declare(strict_types=1);

namespace venndev\vosaka\core\interfaces;

use Generator;

interface ISocket
{
    /**
     * Establish connection to the socket
     */
    public function connect(): Generator;

    /**
     * Disconnect from the socket
     */
    public function disconnect(): Generator;

    /**
     * Send data through the socket
     */
    public function send(string $data): Generator;

    /**
     * Receive data from the socket
     */
    public function receive(): Generator;

    /**
     * Check if socket is connected
     */
    public function isConnected(): bool;

    /**
     * Get connection information
     */
    public function getConnectionInfo(): array;

    /**
     * Register event handler
     */
    public function on(string $event, callable $handler): void;

    /**
     * Remove event handler
     */
    public function off(string $event, ?callable $handler = null): void;

    /**
     * Enable auto-reconnection
     */
    public function enableAutoReconnect(int $maxAttempts = 5, int $delay = 1): void;

    /**
     * Disable auto-reconnection
     */
    public function disableAutoReconnect(): void;
}