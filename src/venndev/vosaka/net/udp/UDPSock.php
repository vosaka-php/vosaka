<?php

declare(strict_types=1);

namespace venndev\vosaka\net\udp;

use Generator;
use InvalidArgumentException;
use venndev\vosaka\core\Result;
use venndev\vosaka\VOsaka;

/**
 * UDPSock provides asynchronous UDP socket operations.
 *
 * This class handles UDP socket creation, binding, sending, and receiving
 * with support for both IPv4 and IPv6 protocols. It integrates with the
 * VOsaka event loop for non-blocking operations.
 */
final class UDPSock
{
    private mixed $socket = null;
    private bool $bound = false;
    private string $addr = "";
    private int $port = 0;
    private array $options = [];

    private function __construct(private readonly string $family = "v4")
    {
        $this->options = [
            "reuseaddr" => true,
            "reuseport" => false,
            "broadcast" => false,
        ];
    }

    /**
     * Create a new IPv4 UDP socket.
     *
     * @return self New UDPSock instance for IPv4
     */
    public static function newV4(): self
    {
        return new self("v4");
    }

    /**
     * Create a new IPv6 UDP socket.
     *
     * @return self New UDPSock instance for IPv6
     */
    public static function newV6(): self
    {
        return new self("v6");
    }

    /**
     * Bind the socket to the specified address and port.
     *
     * @param string $addr Address in 'host:port' format
     * @return Result<UDPSock> Result containing this UDPSock instance
     * @throws InvalidArgumentException If binding fails
     */
    public function bind(string $addr): Result
    {
        $fn = function () use ($addr): Generator {
            [$host, $port] = $this->parseAddr($addr);
            $this->addr = $host;
            $this->port = $port;

            $context = $this->createContext();
            $protocol = $this->family === "v6" ? "udp6" : "udp";

            $this->socket = @stream_socket_server(
                "{$protocol}://{$this->addr}:{$this->port}",
                $errno,
                $errstr,
                STREAM_SERVER_BIND,
                $context
            );

            if (!$this->socket) {
                throw new InvalidArgumentException(
                    "Bind failed: $errstr ($errno)"
                );
            }

            VOsaka::getLoop()->getGracefulShutdown()->addSocket($this->socket);
            $this->bound = true;
            $this->configureSocket();

            yield;
            return $this;
        };

        return Result::c($fn());
    }

    /**
     * Send data to a specific address.
     *
     * @param string $data Data to send
     * @param string $addr Address in 'host:port' format
     * @return Result<int> Number of bytes sent
     * @throws InvalidArgumentException If socket is not created or send fails
     */
    public function sendTo(string $data, string $addr): Result
    {
        if (!$this->socket) {
            throw new InvalidArgumentException(
                "Socket must be created before sending"
            );
        }

        [$host, $port] = $this->parseAddr($addr);

        $sendTask = function () use ($data, $host, $port): Generator {
            $result = @stream_socket_sendto(
                $this->socket,
                $data,
                0,
                "{$host}:{$port}"
            );

            if ($result === false || $result === -1) {
                $error = error_get_last();
                throw new InvalidArgumentException(
                    "Send failed: " . ($error["message"] ?? "Unknown error")
                );
            }

            yield;
            return $result;
        };

        return Result::c($sendTask());
    }

    /**
     * Receive data from any address.
     *
     * @param int $maxLength Maximum length of data to receive
     * @return Result<array{data: string, peerAddr: string}> Received data and peer address
     * @throws InvalidArgumentException If socket is not bound or receive fails
     */
    public function receiveFrom(int $maxLength = 65535): Result
    {
        if (!$this->bound) {
            throw new InvalidArgumentException(
                "Socket must be bound before receiving"
            );
        }

        $receiveTask = function () use ($maxLength): Generator {
            $data = @stream_socket_recvfrom(
                $this->socket,
                $maxLength,
                0,
                $peerAddr
            );

            if ($data === false) {
                $error = error_get_last();
                throw new InvalidArgumentException(
                    "Receive failed: " . ($error["message"] ?? "Unknown error")
                );
            }

            yield;
            return ["data" => $data, "peerAddr" => $peerAddr];
        };

        return Result::c($receiveTask());
    }

    /**
     * Set SO_REUSEADDR socket option.
     *
     * @param bool $reuseAddr Whether to enable address reuse
     * @return self This instance for method chaining
     */
    public function setReuseAddr(bool $reuseAddr): self
    {
        $this->options["reuseaddr"] = $reuseAddr;

        if ($this->socket && function_exists('socket_import_stream')) {
            $sock = socket_import_stream($this->socket);
            if ($sock !== false) {
                socket_set_option($sock, SOL_SOCKET, SO_REUSEADDR, $reuseAddr ? 1 : 0);
            }
        }

        return $this;
    }

    /**
     * Set SO_REUSEPORT socket option.
     *
     * @param bool $reusePort Whether to enable port reuse
     * @return self This instance for method chaining
     */
    public function setReusePort(bool $reusePort): self
    {
        $this->options["reuseport"] = $reusePort;

        if ($this->socket && function_exists('socket_import_stream')) {
            $sock = socket_import_stream($this->socket);
            if ($sock !== false && defined('SO_REUSEPORT')) {
                socket_set_option($sock, SOL_SOCKET, SO_REUSEPORT, $reusePort ? 1 : 0);
            }
        }

        return $this;
    }

    /**
     * Set SO_BROADCAST socket option.
     *
     * @param bool $broadcast Whether to enable broadcast
     * @return self This instance for method chaining
     */
    public function setBroadcast(bool $broadcast): self
    {
        $this->options["broadcast"] = $broadcast;

        if ($this->socket && function_exists('socket_import_stream')) {
            $sock = socket_import_stream($this->socket);
            if ($sock !== false) {
                socket_set_option($sock, SOL_SOCKET, SO_BROADCAST, $broadcast ? 1 : 0);
            }
        }

        return $this;
    }

    /**
     * Get the local address of the bound socket.
     *
     * @return string Local address or empty string if not bound
     */
    public function getLocalAddr(): string
    {
        if (!$this->socket) {
            return "";
        }

        $name = stream_socket_get_name($this->socket, false);
        return $name ?: "";
    }

    /**
     * Check if the socket is closed.
     *
     * @return bool True if socket is closed
     */
    public function isClosed(): bool
    {
        return !$this->socket;
    }

    /**
     * Close the socket and cleanup resources.
     */
    public function close(): void
    {
        if ($this->socket) {
            VOsaka::getLoop()
                ->getGracefulShutdown()
                ->removeSocket($this->socket);
            @fclose($this->socket);
            $this->socket = null;
        }

        $this->bound = false;
    }

    /**
     * Parse address string into host and port components.
     *
     * @param string $addr Address in 'host:port' format
     * @return array{string, int} Array containing host and port
     * @throws InvalidArgumentException If address format is invalid
     */
    private function parseAddr(string $addr): array
    {
        if (strpos($addr, ":") === false) {
            throw new InvalidArgumentException(
                "Invalid address format. Expected 'host:port'"
            );
        }

        $parts = explode(":", $addr);
        $port = (int) array_pop($parts);
        $host = implode(":", $parts);

        if ($port < 1 || $port > 65535) {
            throw new InvalidArgumentException(
                "Port must be between 1 and 65535: {$port}"
            );
        }

        return [$host, $port];
    }

    /**
     * Create stream context with socket options.
     *
     * @return resource Stream context
     */
    private function createContext()
    {
        $context = stream_context_create();

        if ($this->options["reuseaddr"]) {
            stream_context_set_option($context, "socket", "so_reuseaddr", 1);
        }

        if ($this->options["reuseport"]) {
            stream_context_set_option($context, "socket", "so_reuseport", 1);
        }

        if ($this->options["broadcast"]) {
            stream_context_set_option($context, "socket", "so_broadcast", 1);
        }

        return $context;
    }

    /**
     * Configure socket options after creation.
     */
    private function configureSocket(): void
    {
        if (!$this->socket) {
            return;
        }

        stream_set_blocking($this->socket, false);

        if (!function_exists('socket_import_stream')) {
            return;
        }

        $sock = socket_import_stream($this->socket);
        if ($sock === false) {
            return;
        }

        if ($this->options["reuseaddr"]) {
            socket_set_option($sock, SOL_SOCKET, SO_REUSEADDR, 1);
        }

        if ($this->options["reuseport"] && defined('SO_REUSEPORT')) {
            socket_set_option($sock, SOL_SOCKET, SO_REUSEPORT, 1);
        }

        if ($this->options["broadcast"]) {
            socket_set_option($sock, SOL_SOCKET, SO_BROADCAST, 1);
        }
    }
}