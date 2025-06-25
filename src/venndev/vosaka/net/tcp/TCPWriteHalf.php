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

    public function write(string $data): Result
    {
        return $this->stream->write($data);
    }

    public function writeAll(string $data): Result
    {
        return $this->stream->writeAll($data);
    }

    public function flush(): Result
    {
        return $this->stream->flush();
    }

    public function peerAddr(): string
    {
        return $this->stream->peerAddr();
    }
}