<?php

declare(strict_types=1);

namespace venndev\vosaka\net\tcp;

use venndev\vosaka\utils\Result;

final class TCPReadHalf
{
    public function __construct(private TCPStream $stream)
    {
        // TODO: Implement the logic for handling read half of the TCP stream.
    }

    public function read(int $maxBytes = null): Result
    {
        return $this->stream->read($maxBytes);
    }

    public function readExact(int $bytes): Result
    {
        return $this->stream->readExact($bytes);
    }

    public function readUntil(string $delimiter): Result
    {
        return $this->stream->readUntil($delimiter);
    }

    public function readLine(): Result
    {
        return $this->stream->readLine();
    }

    public function peerAddr(): string
    {
        return $this->stream->peerAddr();
    }
}