<?php

declare(strict_types=1);

namespace venndev\vosaka\net\tcp;

use Generator;

final class TCPReadHalf
{
    public function __construct(private TCPStream $stream)
    {
        // TODO: Implement the logic for handling read half of the TCP stream.
    }

    public function read(int $maxBytes = null): Generator
    {
        return yield from $this->stream->read($maxBytes);
    }

    public function readExact(int $bytes): Generator
    {
        return yield from $this->stream->readExact($bytes);
    }

    public function readUntil(string $delimiter): Generator
    {
        return yield from $this->stream->readUntil($delimiter);
    }

    public function readLine(): Generator
    {
        return yield from $this->stream->readLine();
    }

    public function peerAddr(): string
    {
        return $this->stream->peerAddr();
    }
}