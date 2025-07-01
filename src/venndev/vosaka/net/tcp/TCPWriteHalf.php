<?php

declare(strict_types=1);

namespace venndev\vosaka\net\tcp;

use venndev\vosaka\core\Result;

final class TCPWriteHalf
{
    public function __construct(private TCPStream $stream)
    {
        // TODO: Implement the logic for handling write half of the TCP stream.
    }

    /**
     * Write data to the TCP stream
     *
     * @param string $data Data to write
     * @return Result<int> Number of bytes written
     */
    public function write(string $data): Result
    {
        return $this->stream->write($data);
    }

    /**
     * Write all data to the TCP stream
     *
     * @param string $data Data to write
     * @return Result<int> Number of bytes written
     */
    public function writeAll(string $data): Result
    {
        return $this->stream->writeAll($data);
    }

    /**
     * Flush the TCP stream
     *
     * @return Result<void> Result indicating success or failure
     */
    public function flush(): Result
    {
        return $this->stream->flush();
    }

    public function peerAddr(): string
    {
        return $this->stream->peerAddr();
    }
}
