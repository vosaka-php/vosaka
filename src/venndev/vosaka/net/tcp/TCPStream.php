<?php

declare(strict_types=1);

namespace venndev\vosaka\net\tcp;

use venndev\vosaka\net\NetworkConstants;
use venndev\vosaka\net\StreamBase;
use venndev\vosaka\VOsaka;

final class TCPStream extends StreamBase
{
    public function __construct(
        mixed $socket,
        private readonly string $peerAddr
    ) {
        $this->socket = $socket;
        $this->bufferSize = NetworkConstants::TCP_READ_BUFFER_SIZE;
        self::addToEventLoop($socket);
        VOsaka::getLoop()->addReadStream($socket, [$this, "handleRead"]);
    }

    /**
     * Handles reading data from the TCP socket.
     * This method is called by the event loop when the socket is ready for reading.
     */
    public function handleRead(): void
    {
        if ($this->isClosed) {
            return;
        }

        $readCount = 0;
        $totalRead = 0;
        $maxReadCycles = NetworkConstants::TCP_MAX_READ_CYCLES;
        $maxBytesPerCycle = NetworkConstants::TCP_MAX_BYTES_PER_CYCLE;

        while ($readCount < $maxReadCycles && $totalRead < $maxBytesPerCycle) {
            $data = @fread($this->socket, $this->bufferSize);

            if ($data === false || ($data === "" && feof($this->socket))) {
                $this->close();
                return;
            }

            if ($data === "") {
                break;
            }

            $this->readBuffer .= $data;
            $totalRead += strlen($data);
            $readCount++;

            if (strlen($data) < $this->bufferSize) {
                break;
            }
        }
    }

    /**
     * Handles write operations for the TCP stream.
     * This method is called by the event loop when the socket is ready for writing.
     */
    public function handleWrite(): void
    {
        if ($this->isClosed || empty($this->writeBuffer)) {
            VOsaka::getLoop()->removeWriteStream($this->socket);
            $this->writeRegistered = false;
            return;
        }

        $writeCount = 0;
        $maxWriteCycles = NetworkConstants::TCP_MAX_WRITE_CYCLES;

        while (!empty($this->writeBuffer) && $writeCount < $maxWriteCycles) {
            $bytesWritten = @fwrite($this->socket, $this->writeBuffer);

            if ($bytesWritten === false) {
                $this->close();
                return;
            }

            if ($bytesWritten === 0) {
                break;
            }

            $this->writeBuffer = substr($this->writeBuffer, $bytesWritten);
            $writeCount++;
        }

        if (empty($this->writeBuffer)) {
            VOsaka::getLoop()->removeWriteStream($this->socket);
            $this->writeRegistered = false;
        }
    }

    /**
     * Returns the peer address of the TCP connection.
     * This is typically the address of the remote host.
     *
     * @return string The peer address.
     */
    public function peerAddr(): string
    {
        return $this->peerAddr;
    }

    /**
     * Splits the TCP stream into read and write halves.
     * This allows for separate handling of reading and writing operations.
     *
     * @return array<TCPReadHalf|TCPWriteHalf> An array containing the read and write halves of the stream.
     */
    public function split(): array
    {
        return [
            new TCPReadHalf($this->socket, $this->peerAddr),
            new TCPWriteHalf($this->socket, $this->peerAddr),
        ];
    }
}
