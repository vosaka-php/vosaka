<?php

declare(strict_types=1);

namespace venndev\vosaka\net\tcp;

use venndev\vosaka\core\Result;

final class TCPReadHalf
{
    public function __construct(private TCPStream $stream)
    {
        // TODO: Implement the logic for handling read half of the TCP stream.
    }

    /**
     * Read data from the TCP stream
     *
     * @param int|null $maxBytes Maximum number of bytes to read, or null for no limit
     * @return Result<string|null> Data read from the stream, or null if the stream is closed
     */
    public function read(int $maxBytes = null): Result
    {
        return $this->stream->read($maxBytes);
    }

    /**
     * Read exact number of bytes from the TCP stream
     * @param int $bytes Number of bytes to read
     * @return Result<string> Data read from the stream
     */
    public function readExact(int $bytes): Result
    {
        return $this->stream->readExact($bytes);
    }

    /**
     * Write data to the TCP stream
     * @param string $data Data to write
     * @return Result<int> Number of bytes written
     */
    public function readUntil(string $delimiter): Result
    {
        return $this->stream->readUntil($delimiter);
    }

    /**
     * Read a line from the TCP stream
     * @return Result<string|null> Line read from the stream, or null if closed
     */
    public function readLine(): Result
    {
        return $this->stream->readLine();
    }

    public function peerAddr(): string
    {
        return $this->stream->peerAddr();
    }
}