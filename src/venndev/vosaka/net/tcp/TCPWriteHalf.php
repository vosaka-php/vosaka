<?php

declare(strict_types=1);

namespace venndev\vosaka\net\tcp;

use Generator;

final class TCPWriteHalf
{
    public function __construct(private TCPStream $stream)
    {
    }

    public function write(string $data): Generator
    {
        return yield from $this->stream->write($data);
    }

    public function writeAll(string $data): Generator
    {
        return yield from $this->stream->writeAll($data);
    }

    public function flush(): Generator
    {
        return yield from $this->stream->flush();
    }

    public function peerAddr(): string
    {
        return $this->stream->peerAddr();
    }
}