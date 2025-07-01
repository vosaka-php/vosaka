<?php

declare(strict_types=1);

namespace venndev\vosaka\net\tcp;

use Generator;
use InvalidArgumentException;
use SplQueue;
use venndev\vosaka\core\Result;
use venndev\vosaka\VOsaka;

final class TCPStream
{
    private bool $isClosed = false;
    private int $bufferSize = 8192;

    // Event-driven queues
    private SplQueue $readQueue;
    private SplQueue $writeQueue;

    // Internal buffers
    private string $readBuffer = "";
    private bool $isReading = false;
    private bool $isWriting = false;

    // Event callbacks
    private array $readCallbacks = [];
    private array $writeCallbacks = [];

    public function __construct(
        private mixed $socket,
        private readonly string $peerAddr
    ) {
        stream_set_blocking($socket, false);
        $this->readQueue = new SplQueue();
        $this->writeQueue = new SplQueue();

        // Register socket with event loop for proper event handling
        VOsaka::getLoop()->addReadStream($this->socket, [$this, "handleRead"]);
        VOsaka::getLoop()->addWriteStream($this->socket, [
            $this,
            "handleWrite",
        ]);
        VOsaka::getLoop()->getGracefulShutdown()->addSocket($socket);
    }

    /**
     * Event-driven read handler
     */
    public function handleRead(): void
    {
        if ($this->isClosed) {
            return;
        }

        $data = @fread($this->socket, $this->bufferSize);

        if ($data === false) {
            $this->handleError("Read error");
            return;
        }

        if ($data === "" && feof($this->socket)) {
            $this->handleConnectionClosed();
            return;
        }

        if ($data !== "") {
            $this->readBuffer .= $data;
            $this->processReadQueue();
        }
    }

    /**
     * Event-driven write handler
     */
    public function handleWrite(): void
    {
        if ($this->isClosed || $this->writeQueue->isEmpty()) {
            return;
        }

        $writeOp = $this->writeQueue->bottom();
        $bytesWritten = @fwrite($this->socket, $writeOp["data"]);

        if ($bytesWritten === false) {
            $this->handleError("Write error");
            return;
        }

        $writeOp["written"] += $bytesWritten;
        $writeOp["data"] = substr($writeOp["data"], $bytesWritten);

        if (empty($writeOp["data"])) {
            $this->writeQueue->dequeue();
            $writeOp["resolver"]($writeOp["written"]);
        }

        if ($this->writeQueue->isEmpty()) {
            VOsaka::getLoop()->removeWriteStream($this->socket);
        }
    }

    /**
     * Process pending read operations
     */
    private function processReadQueue(): void
    {
        while (!$this->readQueue->isEmpty() && !empty($this->readBuffer)) {
            $readOp = $this->readQueue->bottom();

            switch ($readOp["type"]) {
                case "read":
                    $this->processRead($readOp);
                    break;
                case "readExact":
                    $this->processReadExact($readOp);
                    break;
                case "readUntil":
                    $this->processReadUntil($readOp);
                    break;
            }
        }
    }

    private function processRead(array $readOp): void
    {
        $maxBytes = $readOp["maxBytes"] ?? $this->bufferSize;
        $data = substr($this->readBuffer, 0, $maxBytes);
        $this->readBuffer = substr($this->readBuffer, strlen($data));

        $this->readQueue->dequeue();
        $readOp["resolver"]($data);
    }

    private function processReadExact(array $readOp): void
    {
        if (strlen($this->readBuffer) >= $readOp["bytes"]) {
            $data = substr($this->readBuffer, 0, $readOp["bytes"]);
            $this->readBuffer = substr($this->readBuffer, $readOp["bytes"]);

            $this->readQueue->dequeue();
            $readOp["resolver"]($data);
        }
    }

    private function processReadUntil(array $readOp): void
    {
        $delimiter = $readOp["delimiter"];
        $pos = strpos($this->readBuffer, $delimiter);

        if ($pos !== false) {
            $data = substr($this->readBuffer, 0, $pos);
            $this->readBuffer = substr(
                $this->readBuffer,
                $pos + strlen($delimiter)
            );

            $this->readQueue->dequeue();
            $readOp["resolver"]($data);
        }
    }

    /**
     * Non-blocking read with event-driven approach
     */
    public function read(int|null $maxBytes = null): Result
    {
        $fn = function () use ($maxBytes): Generator {
            if ($this->isClosed) {
                throw new InvalidArgumentException("Stream is closed");
            }

            // Try immediate read from buffer
            if (!empty($this->readBuffer)) {
                $bytes = $maxBytes ?? $this->bufferSize;
                $data = substr($this->readBuffer, 0, $bytes);
                $this->readBuffer = substr($this->readBuffer, strlen($data));
                return $data;
            }

            // Queue read operation and wait for event
            $result = yield from $this->queueReadOperation("read", [
                "maxBytes" => $maxBytes,
            ]);

            return $result;
        };

        return Result::c($fn());
    }

    /**
     * Read exact number of bytes
     */
    public function readExact(int $bytes): Result
    {
        $fn = function () use ($bytes): Generator {
            if ($this->isClosed) {
                throw new InvalidArgumentException("Stream is closed");
            }

            // Check if we already have enough data
            if (strlen($this->readBuffer) >= $bytes) {
                $data = substr($this->readBuffer, 0, $bytes);
                $this->readBuffer = substr($this->readBuffer, $bytes);
                return $data;
            }

            $result = yield from $this->queueReadOperation("readExact", [
                "bytes" => $bytes,
            ]);
            return $result;
        };

        return Result::c($fn());
    }

    /**
     * Read until delimiter
     */
    public function readUntil(string $delimiter): Result
    {
        $fn = function () use ($delimiter): Generator {
            if ($this->isClosed) {
                throw new InvalidArgumentException("Stream is closed");
            }

            // Check if delimiter is already in buffer
            $pos = strpos($this->readBuffer, $delimiter);
            if ($pos !== false) {
                $data = substr($this->readBuffer, 0, $pos);
                $this->readBuffer = substr(
                    $this->readBuffer,
                    $pos + strlen($delimiter)
                );
                return $data;
            }

            $result = yield from $this->queueReadOperation("readUntil", [
                "delimiter" => $delimiter,
            ]);
            return $result;
        };

        return Result::c($fn());
    }

    /**
     * Queue read operation and wait for completion
     */
    private function queueReadOperation(string $type, array $params): Generator
    {
        $resolver = null;
        $promise = new class {
            public $resolver;
            public $result;
            public $completed = false;
        };

        $promise->resolver = function ($result) use ($promise) {
            $promise->result = $result;
            $promise->completed = true;
        };

        $this->readQueue->enqueue([
            "type" => $type,
            "resolver" => $promise->resolver,
            ...$params,
        ]);

        // Wait for operation to complete
        while (!$promise->completed && !$this->isClosed) {
            yield; // Yield control to event loop
        }

        if ($this->isClosed) {
            throw new InvalidArgumentException("Stream closed during read");
        }

        return $promise->result;
    }

    /**
     * Event-driven write
     */
    public function write(string $data): Result
    {
        $fn = function () use ($data): Generator {
            if ($this->isClosed) {
                throw new InvalidArgumentException("Stream is closed");
            }

            $promise = new class {
                public $resolver;
                public $result;
                public $completed = false;
            };

            $promise->resolver = function ($result) use ($promise): void {
                $promise->result = $result;
                $promise->completed = true;
            };

            $this->writeQueue->enqueue([
                "data" => $data,
                "written" => 0,
                "resolver" => $promise->resolver,
            ]);

            // Enable write events
            VOsaka::getLoop()->addWriteStream($this->socket, [
                $this,
                "handleWrite",
            ]);

            // Wait for write to complete
            while (!$promise->completed && !$this->isClosed) {
                yield;
            }

            if ($this->isClosed) {
                throw new InvalidArgumentException(
                    "Stream closed during write"
                );
            }

            return $promise->result;
        };

        return Result::c($fn());
    }

    /**
     * Handle connection errors
     */
    private function handleError(string $error): void
    {
        // Notify all pending operations
        while (!$this->readQueue->isEmpty()) {
            $op = $this->readQueue->dequeue();
            $op["resolver"](null);
        }

        while (!$this->writeQueue->isEmpty()) {
            $op = $this->writeQueue->dequeue();
            $op["resolver"](0);
        }

        $this->close();
    }

    /**
     * Handle connection closed
     */
    private function handleConnectionClosed(): void
    {
        $this->handleError("Connection closed");
    }

    public function readLine(): Result
    {
        return $this->readUntil("\n");
    }

    public function writeAll(string $data): Result
    {
        return $this->write($data);
    }

    public function flush(): Result
    {
        $fn = function (): Generator {
            if ($this->socket && !$this->isClosed) {
                @fflush($this->socket);
            }
            yield;
        };

        return Result::c($fn());
    }

    public function peerAddr(): string
    {
        return $this->peerAddr;
    }

    public function close(): void
    {
        if (!$this->isClosed) {
            $this->isClosed = true;

            VOsaka::getLoop()->removeReadStream($this->socket);
            VOsaka::getLoop()->removeWriteStream($this->socket);
            VOsaka::getLoop()
                ->getGracefulShutdown()
                ->removeSocket($this->socket);

            @fclose($this->socket);
            $this->socket = null;
        }
    }

    public function isClosed(): bool
    {
        return $this->isClosed;
    }

    public function split(): array
    {
        return [
            new TCPReadHalf($this->socket),
            new TCPWriteHalf($this->socket),
        ];
    }
}
