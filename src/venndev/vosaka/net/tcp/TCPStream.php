<?php

declare(strict_types=1);

namespace venndev\vosaka\net\tcp;

use Generator;
use InvalidArgumentException;
use venndev\vosaka\time\Sleep;

final class TCPStream
{
    private bool $isClosed = false;
    private int $bufferSize = 8192;

    public function __construct(
        private mixed $socket,
        private readonly string $peerAddr
    ) {
        // Auto-cleanup on destruction
        register_shutdown_function(function () {
            $this->close();
        });
    }

    /**
     * Read data from stream
     */
    public function read(int $maxBytes = null): Generator
    {
        if ($this->isClosed) {
            throw new InvalidArgumentException("Stream is closed");
        }

        $maxBytes = $maxBytes ?? $this->bufferSize;

        while (true) {
            $data = @fread($this->socket, $maxBytes);

            if ($data === false || ($data === '' && feof($this->socket))) {
                return null; // Connection closed
            }

            if ($data !== '') {
                return $data;
            }

            yield Sleep::c(0.001); // Non-blocking wait
        }
    }

    /**
     * Read exact number of bytes
     */
    public function readExact(int $bytes): Generator
    {
        $buffer = '';
        $remaining = $bytes;

        while ($remaining > 0) {
            $chunk = yield from $this->read($remaining);

            if ($chunk === null) {
                throw new InvalidArgumentException("Connection closed before reading exact bytes");
            }

            $buffer .= $chunk;
            $remaining -= strlen($chunk);
        }

        return $buffer;
    }

    /**
     * Read until delimiter
     */
    public function readUntil(string $delimiter): Generator
    {
        $buffer = '';

        while (true) {
            $chunk = yield from $this->read(1);

            if ($chunk === null) {
                return $buffer ?: null;
            }

            $buffer .= $chunk;

            if (str_ends_with($buffer, $delimiter)) {
                return substr($buffer, 0, -strlen($delimiter));
            }
        }
    }

    /**
     * Read line (until \n)
     */
    public function readLine(): Generator
    {
        return yield from $this->readUntil("\n");
    }

    /**
     * Write data to stream
     */
    public function write(string $data): Generator
    {
        if ($this->isClosed) {
            throw new InvalidArgumentException("Stream is closed");
        }

        $totalBytes = strlen($data);
        $bytesWritten = 0;

        while ($bytesWritten < $totalBytes) {
            $result = @fwrite($this->socket, substr($data, $bytesWritten));

            if ($result === false) {
                throw new InvalidArgumentException("Write failed");
            }

            $bytesWritten += $result;

            if ($bytesWritten < $totalBytes) {
                yield Sleep::c(0.001);
            }
        }

        return $bytesWritten;
    }

    /**
     * Write all data (ensures complete write)
     */
    public function writeAll(string $data): Generator
    {
        return yield from $this->write($data);
    }

    /**
     * Flush the stream
     */
    public function flush(): Generator
    {
        if ($this->socket) {
            @fflush($this->socket);
        }
        yield Sleep::c(0.001);
    }

    /**
     * Get peer address
     */
    public function peerAddr(): string
    {
        return $this->peerAddr;
    }

    /**
     * Close the stream
     */
    public function close(): void
    {
        if ($this->socket && !$this->isClosed) {
            @fclose($this->socket);
            $this->socket = null;
            $this->isClosed = true;
        }
    }

    public function isClosed(): bool
    {
        return $this->isClosed;
    }

    /**
     * Split stream into reader and writer
     */
    public function split(): array
    {
        return [
            new TCPReadHalf($this),
            new TCPWriteHalf($this)
        ];
    }
}