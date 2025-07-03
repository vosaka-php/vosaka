<?php

declare(strict_types=1);

namespace venndev\vosaka\net\tcp;

use venndev\vosaka\net\StreamBase;
use venndev\vosaka\VOsaka;

final class TCPStream extends StreamBase
{
    public function __construct(
        mixed $socket,
        private readonly string $peerAddr
    ) {
        $this->socket = $socket;
        $this->bufferSize = 524288; // 512KB
        self::addToEventLoop($socket);
        VOsaka::getLoop()->addReadStream($socket, [$this, "handleRead"]);
    }

    public function handleRead(): void
    {
        if ($this->isClosed) {
            return;
        }

        $readCount = 0;
        $totalRead = 0;
        $maxReadCycles = 10;
        $maxBytesPerCycle = 2097152; // 2MB

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

    public function handleWrite(): void
    {
        if ($this->isClosed || empty($this->writeBuffer)) {
            VOsaka::getLoop()->removeWriteStream($this->socket);
            $this->writeRegistered = false;
            return;
        }

        $writeCount = 0;
        $maxWriteCycles = 5;

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

    public function peerAddr(): string
    {
        return $this->peerAddr;
    }

    public function split(): array
    {
        return [
            new TCPReadHalf($this->socket, $this->peerAddr),
            new TCPWriteHalf($this->socket, $this->peerAddr),
        ];
    }
}
