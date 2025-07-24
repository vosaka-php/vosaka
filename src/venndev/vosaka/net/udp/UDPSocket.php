<?php

declare(strict_types=1);

namespace venndev\vosaka\net\udp;

use venndev\vosaka\core\Result;
use venndev\vosaka\core\Future;
use venndev\vosaka\net\contracts\AddressInterface;
use venndev\vosaka\net\contracts\DatagramInterface;
use venndev\vosaka\net\exceptions\NetworkException;
use venndev\vosaka\net\tcp\TCPAddress;
use venndev\vosaka\net\EventLoopIntegration;
use Generator;
use Exception;

/**
 * UDP Socket implementation
 */
class UDPSocket implements DatagramInterface
{
    private $socket;
    private bool $closed = false;
    private ?AddressInterface $localAddress = null;
    private EventLoopIntegration $eventLoop;
    private array $receiveQueue = [];
    private bool $readRegistered = false;

    public function __construct($socket)
    {
        if (!is_resource($socket)) {
            throw new NetworkException("Invalid socket resource");
        }

        $this->socket = $socket;
        $this->eventLoop = new EventLoopIntegration();

        // Set non-blocking
        stream_set_blocking($socket, false);

        // Register read handler
        $this->eventLoop->onReadable($socket, [$this, 'handleRead']);
        $this->readRegistered = true;
    }

    /**
     * Handle readable event
     */
    public function handleRead($socket): void
    {
        if ($this->closed) {
            return;
        }

        // Read all available datagrams
        while (true) {
            $data = @stream_socket_recvfrom($socket, 65535, 0, $peer);

            if ($data === false || $data === '') {
                break;
            }

            // Parse peer address
            $peerAddress = null;
            if ($peer) {
                try {
                    $peerAddress = TCPAddress::parse($peer);
                } catch (Exception) {
                    // Invalid peer address, skip
                    continue;
                }
            }

            // Add to receive queue
            $this->receiveQueue[] = [
                'data' => $data,
                'address' => $peerAddress
            ];
        }
    }

    /**
     * Send data to a specific address
     * @param string $data The data to send
     * @param AddressInterface $address The destination address
     * @return Result<int>
     */
    public function sendTo(string $data, AddressInterface $address): Result
    {
        return Future::new($this->doSendTo($data, $address));
    }

    /**
     * Send data to a specific address
     * @param string $data The data to send
     * @param AddressInterface $address The destination address
     * @return Generator<int>
     * @throws NetworkException
     */
    private function doSendTo(string $data, AddressInterface $address): Generator
    {
        if ($this->closed) {
            throw new NetworkException("Socket is closed");
        }

        if (empty($data)) {
            return 0;
        }

        // Format address for stream_socket_sendto
        $peer = $address->toString();

        $sent = @stream_socket_sendto($this->socket, $data, 0, $peer);

        if ($sent === false) {
            $error = error_get_last();
            throw new NetworkException("Failed to send datagram: " . ($error['message'] ?? 'Unknown error'));
        }

        yield;

        return $sent;
    }

    /**
     * Receive data from any address
     * @param int $maxLength Maximum length of data to receive
     * @return Result<array{data: string, address: AddressInterface|null}>
     */
    public function receiveFrom(int $maxLength = 65535): Result
    {
        return Future::new($this->doReceiveFrom($maxLength));
    }

    /**
     * Receive data from any address
     * @param int $maxLength Maximum length of data to receive
     * @return Generator<array{data: string, address: AddressInterface|null}>
     * @throws NetworkException
     */
    private function doReceiveFrom(int $maxLength): Generator
    {
        if ($this->closed) {
            throw new NetworkException("Socket is closed");
        }

        // Check queue first
        if (!empty($this->receiveQueue)) {
            $packet = array_shift($this->receiveQueue);
            return [
                'data' => substr($packet['data'], 0, $maxLength),
                'address' => $packet['address']
            ];
        }

        // Wait for data
        while (empty($this->receiveQueue) && !$this->closed) {
            yield;
        }

        if ($this->closed) {
            throw new NetworkException("Socket closed while receiving");
        }

        $packet = array_shift($this->receiveQueue);
        return [
            'data' => substr($packet['data'], 0, $maxLength),
            'address' => $packet['address']
        ];
    }

    /**
     * Get the local address of the socket
     * @return AddressInterface
     */
    public function getLocalAddress(): AddressInterface
    {
        if ($this->localAddress === null && is_resource($this->socket)) {
            $name = stream_socket_get_name($this->socket, false);
            if ($name !== false) {
                $this->localAddress = TCPAddress::parse($name);
            }
        }

        return $this->localAddress;
    }

    /**
     * Close the socket and clean up resources
     */
    public function close(): void
    {
        if ($this->closed) {
            return;
        }

        $this->closed = true;

        if ($this->readRegistered) {
            $this->eventLoop->removeReadable($this->socket);
            $this->readRegistered = false;
        }

        if (is_resource($this->socket)) {
            @fclose($this->socket);
        }

        $this->receiveQueue = [];
    }

    /**
     * Check if the socket is closed
     */
    public function isClosed(): bool
    {
        return $this->closed || !is_resource($this->socket);
    }

    /**
     * Set socket option
     */
    public function setOption(int $level, int $option, mixed $value): void
    {
        if (!function_exists('socket_import_stream')) {
            return;
        }

        $sock = @socket_import_stream($this->socket);
        if ($sock !== false) {
            @socket_set_option($sock, $level, $option, $value);
        }
    }

    /**
     * Enable broadcast
     */
    public function setBroadcast(bool $enable): void
    {
        $this->setOption(SOL_SOCKET, SO_BROADCAST, $enable ? 1 : 0);
    }

    /**
     * Set multicast TTL
     */
    public function setMulticastTTL(int $ttl): void
    {
        $this->setOption(IPPROTO_IP, IP_MULTICAST_TTL, $ttl);
    }

    /**
     * Join multicast group
     */
    public function joinMulticastGroup(string $group, string $interface = '0.0.0.0'): void
    {
        if (!function_exists('socket_import_stream')) {
            throw new NetworkException("Multicast requires sockets extension");
        }

        $sock = socket_import_stream($this->socket);
        if ($sock === false) {
            throw new NetworkException("Failed to import socket");
        }

        $mreq = ['group' => $group, 'interface' => $interface];
        if (!@socket_set_option($sock, IPPROTO_IP, MCAST_JOIN_GROUP, $mreq)) {
            throw new NetworkException("Failed to join multicast group");
        }
    }

    /**
     * Leave multicast group
     */
    public function leaveMulticastGroup(string $group, string $interface = '0.0.0.0'): void
    {
        if (!function_exists('socket_import_stream')) {
            return;
        }

        $sock = @socket_import_stream($this->socket);
        if ($sock !== false) {
            $mreq = ['group' => $group, 'interface' => $interface];
            @socket_set_option($sock, IPPROTO_IP, MCAST_LEAVE_GROUP, $mreq);
        }
    }
}
