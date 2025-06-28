<?php

declare(strict_types=1);

namespace venndev\vosaka\net\unix;

use venndev\vosaka\core\Result;

/**
 * Write half of a Unix domain socket stream.
 *
 * This class represents the write-only half of a Unix domain socket stream,
 * created by splitting a UnixStream. It provides write-only access to the
 * underlying socket while maintaining the same async interface.
 */
final class UnixWriteHalf
{
    public function __construct(
        private readonly UnixStream $stream
    ) {
    }

    /**
     * Write data to stream
     * @param string $data Data to write
     * @return Result<int> Number of bytes written
     */
    public function write(string $data): Result
    {
        return $this->stream->write($data);
    }

    /**
     * Write all data (ensures complete write)
     * @param string $data Data to write
     * @return Result<int> Number of bytes written
     */
    public function writeAll(string $data): Result
    {
        return $this->stream->writeAll($data);
    }

    /**
     * Flush the stream
     * @return Result<void>
     */
    public function flush(): Result
    {
        return $this->stream->flush();
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
