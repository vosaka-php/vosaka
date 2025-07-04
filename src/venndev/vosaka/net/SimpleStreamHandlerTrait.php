<?php

declare(strict_types=1);

namespace venndev\vosaka\net;

use InvalidArgumentException;
use venndev\vosaka\VOsaka;

trait SimpleStreamHandlerTrait
{
    protected bool $readRegistered = false;
    protected bool $writeRegistered = false;

    /**
     * Simple read handler that doesn't over-detect errors
     */
    protected function performRead(): void
    {
        if ($this->isClosed || !$this->socket || !is_resource($this->socket)) {
            return;
        }

        $readCount = 0;
        $totalRead = 0;
        $maxReadCycles = $this->getMaxReadCycles();
        $maxBytesPerCycle = $this->getMaxBytesPerCycle();

        while ($readCount < $maxReadCycles && $totalRead < $maxBytesPerCycle) {
            $data = @fread($this->socket, $this->bufferSize);

            // Only close on EOF or definitive errors
            if ($data === false && feof($this->socket)) {
                $this->close();
                return;
            }

            if ($data === "" && feof($this->socket)) {
                $this->close();
                return;
            }

            if ($data === "" || $data === false) {
                // No data available or temporary error, just break
                break;
            }

            $this->readBuffer .= $data;
            $totalRead += strlen($data);
            $readCount++;

            // If we read less than buffer size, socket is likely empty
            if (strlen($data) < $this->bufferSize) {
                break;
            }
        }
    }

    /**
     * Simple write handler that doesn't over-detect errors
     */
    protected function performWrite(): void
    {
        if (
            $this->isClosed ||
            empty($this->writeBuffer) ||
            !$this->socket ||
            !is_resource($this->socket)
        ) {
            $this->unregisterWriteHandler();
            return;
        }

        $writeCount = 0;
        $maxWriteCycles = $this->getMaxWriteCycles();
        $chunkSize = $this->getWriteChunkSize();

        while (!empty($this->writeBuffer) && $writeCount < $maxWriteCycles) {
            $chunk = substr($this->writeBuffer, 0, $chunkSize);
            $bytesWritten = @fwrite($this->socket, $chunk);

            // Only close on EOF or definitive errors
            if ($bytesWritten === false && feof($this->socket)) {
                $this->close();
                return;
            }

            if ($bytesWritten === false || $bytesWritten === 0) {
                // Socket not ready for writing, try again later
                break;
            }

            $this->writeBuffer = substr($this->writeBuffer, $bytesWritten);
            $writeCount++;
        }

        if (empty($this->writeBuffer)) {
            $this->unregisterWriteHandler();
        }
    }

    /**
     * Safely register read handler with the event loop
     */
    protected function registerReadHandler(): void
    {
        if (
            !$this->readRegistered &&
            $this->socket &&
            is_resource($this->socket)
        ) {
            VOsaka::getLoop()->addReadStream($this->socket, [
                $this,
                "handleRead",
            ]);
            $this->readRegistered = true;
        }
    }

    /**
     * Safely unregister read handler from the event loop
     */
    protected function unregisterReadHandler(): void
    {
        if (
            $this->readRegistered &&
            $this->socket &&
            is_resource($this->socket)
        ) {
            VOsaka::getLoop()->removeReadStream($this->socket);
            $this->readRegistered = false;
        }
    }

    /**
     * Safely register write handler with the event loop
     */
    protected function registerWriteHandler(): void
    {
        if (
            !$this->writeRegistered &&
            $this->socket &&
            is_resource($this->socket)
        ) {
            VOsaka::getLoop()->addWriteStream($this->socket, [
                $this,
                "handleWrite",
            ]);
            $this->writeRegistered = true;
        }
    }

    /**
     * Safely unregister write handler from the event loop
     */
    protected function unregisterWriteHandler(): void
    {
        if (
            $this->writeRegistered &&
            $this->socket &&
            is_resource($this->socket)
        ) {
            VOsaka::getLoop()->removeWriteStream($this->socket);
            $this->writeRegistered = false;
        }
    }

    /**
     * Check if socket is in a healthy state
     */
    protected function isSocketHealthy(): bool
    {
        if (!$this->socket || !is_resource($this->socket)) {
            return false;
        }

        // Only check for EOF, don't be too strict
        return !feof($this->socket);
    }

    /**
     * Clean up all handlers and socket
     */
    protected function cleanupHandlers(): void
    {
        $this->unregisterReadHandler();
        $this->unregisterWriteHandler();

        if ($this->socket && is_resource($this->socket)) {
            self::removeFromEventLoop($this->socket);
        }
    }

    /**
     * Get maximum read cycles per event loop iteration
     */
    protected function getMaxReadCycles(): int
    {
        return NetworkConstants::MAX_READ_CYCLES ?? 10;
    }

    /**
     * Get maximum bytes to read per cycle
     */
    protected function getMaxBytesPerCycle(): int
    {
        return NetworkConstants::MAX_BYTES_PER_CYCLE ?? 65536;
    }

    /**
     * Get maximum write cycles per event loop iteration
     */
    protected function getMaxWriteCycles(): int
    {
        return NetworkConstants::MAX_WRITE_CYCLES ?? 10;
    }

    /**
     * Get write chunk size for backpressure handling
     */
    protected function getWriteChunkSize(): int
    {
        return min($this->bufferSize, 8192);
    }

    /**
     * Initialize stream with proper socket setup
     */
    protected function initializeStream(
        mixed $socket,
        array $options = []
    ): void {
        if (!$socket || !is_resource($socket)) {
            throw new InvalidArgumentException("Invalid socket resource");
        }

        $this->socket = $socket;
        $this->options = self::normalizeOptions($options);
        $this->bufferSize = $this->options["buffer_size"] ?? 8192;

        stream_set_blocking($socket, false);

        self::applySocketOptions($socket, $this->options);
        self::addToEventLoop($socket);
    }

    /**
     * Validate write operation before executing
     */
    protected function validateWrite(string $data): bool
    {
        if ($this->isClosed) {
            return false;
        }

        if (empty($data)) {
            return false;
        }

        if (!$this->isSocketHealthy()) {
            $this->close();
            return false;
        }

        return true;
    }

    /**
     * Check if we need to register write handler for buffered data
     */
    protected function shouldRegisterWriteHandler(): bool
    {
        return !empty($this->writeBuffer) &&
            !$this->writeRegistered &&
            $this->isSocketHealthy();
    }
}
