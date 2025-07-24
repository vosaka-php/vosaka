<?php

declare(strict_types=1);

namespace venndev\vosaka\net;

use venndev\vosaka\net\exceptions\NetworkException;

/**
 * Stream buffer for managing read/write data
 */
class StreamBuffer
{
    private string $data = '';
    private int $size = 0;
    private int $maxSize;

    public function __construct(int $maxSize = 1048576) // 1MB default
    {
        $this->maxSize = $maxSize;
    }

    /**
     * Set maximum buffer size
     */
    public function append(string $data): void
    {
        if ($this->size + strlen($data) > $this->maxSize) {
            throw new NetworkException("Buffer overflow");
        }

        $this->data .= $data;
        $this->size += strlen($data);
    }

    /**
     * Read data from the buffer
     *
     * @param int $length Number of bytes to read, -1 for all
     * @return string Data read from the buffer
     */
    public function read(int $length = -1): string
    {
        if ($length === -1 || $length >= $this->size) {
            $data = $this->data;
            $this->clear();
            return $data;
        }

        $data = substr($this->data, 0, $length);
        $this->data = substr($this->data, $length);
        $this->size -= $length;

        return $data;
    }

    /**
     * Read data until a specific delimiter
     *
     * @param string $delimiter Delimiter to read until
     * @return string|null Data read until the delimiter, or null if not found
     */
    public function readUntil(string $delimiter): ?string
    {
        $pos = strpos($this->data, $delimiter);
        if ($pos === false) {
            return null;
        }

        $data = substr($this->data, 0, $pos);
        $this->data = substr($this->data, $pos + strlen($delimiter));
        $this->size = strlen($this->data);

        return $data;
    }

    /**
     * Read a line from the buffer
     *
     * @return string|null Line read from the buffer, or null if no line found
     */
    public function readLine(): ?string
    {
        return $this->readUntil("\n");
    }

    /**
     * Peek data in the buffer without removing it
     *
     * @param int $length Number of bytes to peek, -1 for all
     * @return string Data peeked from the buffer
     */
    public function peek(int $length = -1): string
    {
        if ($length === -1) {
            return $this->data;
        }

        return substr($this->data, 0, $length);
    }

    /**
     * Check if the buffer is empty
     *
     * @return bool True if the buffer is empty, false otherwise
     */
    public function isEmpty(): bool
    {
        return $this->size === 0;
    }

    /**
     * Get the current size of the buffer
     *
     * @return int Size of the buffer in bytes
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * Clear the buffer
     */
    public function clear(): void
    {
        $this->data = '';
        $this->size = 0;
    }
}
