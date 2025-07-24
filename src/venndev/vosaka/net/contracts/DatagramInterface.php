<?php

declare(strict_types=1);

namespace venndev\vosaka\net\contracts;

use venndev\vosaka\core\Result;

/**
 * Interface for datagram (UDP) sockets
 */
interface DatagramInterface
{
    /**
     * Send data to address
     * 
     * @param string $data
     * @param AddressInterface $address
     * @return Result<int> Bytes sent
     */
    public function sendTo(string $data, AddressInterface $address): Result;

    /**
     * Receive data from any address
     * 
     * @param int $maxLength
     * @return Result<array{data: string, address: AddressInterface}>
     */
    public function receiveFrom(int $maxLength = 65535): Result;

    /**
     * Get local address
     */
    public function getLocalAddress(): AddressInterface;

    /**
     * Close socket
     */
    public function close(): void;

    /**
     * Check if closed
     */
    public function isClosed(): bool;
}
