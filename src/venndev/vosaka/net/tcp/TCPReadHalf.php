<?php

declare(strict_types=1);

namespace venndev\vosaka\net\tcp;

use InvalidArgumentException;
use venndev\vosaka\core\Result;
use venndev\vosaka\net\StreamBase;
use venndev\vosaka\VOsaka;

final class TCPReadHalf extends StreamBase
{
    public function __construct(
        mixed $socket,
        private readonly string $peerAddr = ""
    ) {
        $this->socket = $socket;
        $this->bufferSize = 524288;
        if ($socket) {
            self::addToEventLoop($socket);
            VOsaka::getLoop()->addReadStream($socket, [$this, "handleRead"]);
        }
    }

    public function handleRead(): void
    {
        if ($this->isClosed || !$this->socket) {
            return;
        }

        $data = @fread($this->socket, $this->bufferSize);

        if ($data === false || ($data === "" && feof($this->socket))) {
            $this->close();
            return;
        }

        $this->readBuffer .= $data;
    }

    public function handleWrite(): void
    {
        // No-op: Read-only stream
    }

    public function peerAddr(): string
    {
        return $this->peerAddr;
    }

    public function write(string $data): Result
    {
        throw new InvalidArgumentException(
            "Write operation not supported on read-only stream"
        );
    }

    public function writeAll(string $data): Result
    {
        throw new InvalidArgumentException(
            "Write operation not supported on read-only stream"
        );
    }

    public function flush(): Result
    {
        throw new InvalidArgumentException(
            "Flush operation not supported on read-only stream"
        );
    }

    public function close(): void
    {
        if (!$this->isClosed && $this->socket) {
            $this->isClosed = true;
            VOsaka::getLoop()->removeReadStream($this->socket);
        }
    }
}
