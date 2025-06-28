<?php

declare(strict_types=1);

namespace venndev\vosaka\net\unix;

use venndev\vosaka\core\Result;

/**
 * Read half of a Unix domain socket stream.
 *
 * This class represents the read-only half of a Unix domain socket stream,
 * created by splitting a UnixStream. It provides read-only access to the
 * underlying socket while maintaining the same async interface.
 */
final class UnixReadHalf
{
    public function __construct(
        private readonly UnixStream $stream
    ) {
    }

    /**
     * Read data from stream
     * @param int|null $maxBytes Maximum bytes to read, null for default buffer size
     * @return Result<string|null> Data read from stream, or null if closed
     */
    public function read(int|null $maxBytes = null): Result
    {
        return $this->stream->read($maxBytes);
    }

    /**
     * Read exact number of bytes
     * @param int $bytes Number of bytes to read
     * @return Result<string> Data read from stream
     */
    public function readExact(int $bytes): Result
    {
        return $this->stream->readExact($bytes);
    }

    /**
     * Read until delimiter
     * @param string $delimiter Delimiter to read until
     * @return Result<string|null> Data read until delimiter, or null if closed
     */
    public function readUntil(string $delimiter): Result
    {
        return $this->stream->readUntil($delimiter);
    }

    /**
     * Read line (until \n)
     * @return Result<string|null> Line read from stream, or null if closed
     */
    public function readLine(): Result
    {
        return $this->stream->readLine();
    }

    /**
     * Get peer path
     */
    public function peerPath(): string
    {
        return $this->stream->peerPath();
    }

    /**
     * Get local path
     */
    public function localPath(): string
    {
        return $this->stream->localPath();
    }

    /**
     * Check if stream is closed
     */
    public function isClosed(): bool
    {
        return $this->stream->isClosed();
    }
}
