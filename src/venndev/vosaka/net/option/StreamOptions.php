<?php

declare(strict_types=1);

namespace venndev\vosaka\net\option;

final class StreamOptions implements StreamOptionsInterface
{
    private array $options = [];

    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    public function setTimeout(int $seconds, int $microseconds = 0): self
    {
        $this->options["timeout"] = $seconds + $microseconds / 1000000;
        return $this;
    }

    public function setBufferSize(int $size): self
    {
        $this->options["buffer_size"] = $size;
        return $this;
    }

    public function setBlocking(bool $blocking): self
    {
        $this->options["blocking"] = $blocking;
        return $this;
    }

    public function setReadTimeout(int $seconds, int $microseconds = 0): self
    {
        $this->options["read_timeout"] = [
            "sec" => $seconds,
            "usec" => $microseconds,
        ];
        return $this;
    }

    public function setWriteTimeout(int $seconds, int $microseconds = 0): self
    {
        $this->options["write_timeout"] = [
            "sec" => $seconds,
            "usec" => $microseconds,
        ];
        return $this;
    }

    public function setChunkSize(int $size): self
    {
        $this->options["chunk_size"] = $size;
        return $this;
    }

    public function toArray(): array
    {
        return $this->options;
    }

    public function merge(StreamOptionsInterface $other): self
    {
        $merged = array_merge($this->options, $other->toArray());
        return new self($merged);
    }
}
