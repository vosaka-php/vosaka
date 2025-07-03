<?php

declare(strict_types=1);

namespace venndev\vosaka\net\option;

final class SocketOptions implements SocketOptionsInterface
{
    private array $options = [];

    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    public function setTimeout(int $seconds, int $microseconds = 0): self
    {
        $this->options["timeout"] = [
            "sec" => $seconds,
            "usec" => $microseconds,
        ];
        return $this;
    }

    public function setReuseAddress(bool $reuse = true): self
    {
        $this->options["reuse_address"] = $reuse;
        return $this;
    }

    public function setReusePort(bool $reuse = true): self
    {
        $this->options["reuse_port"] = $reuse;
        return $this;
    }

    public function setTcpNoDelay(bool $nodelay = true): self
    {
        $this->options["tcp_nodelay"] = $nodelay;
        return $this;
    }

    public function setKeepAlive(bool $keepalive = true): self
    {
        $this->options["keep_alive"] = $keepalive;
        return $this;
    }

    public function setSendBufferSize(int $size): self
    {
        $this->options["send_buffer_size"] = $size;
        return $this;
    }

    public function setReceiveBufferSize(int $size): self
    {
        $this->options["receive_buffer_size"] = $size;
        return $this;
    }

    public function setBlocking(bool $blocking): self
    {
        $this->options["blocking"] = $blocking;
        return $this;
    }

    public function setBacklog(int $backlog): self
    {
        $this->options["backlog"] = $backlog;
        return $this;
    }

    public function setBindAddress(string $address): self
    {
        $this->options["bind_address"] = $address;
        return $this;
    }

    public function toArray(): array
    {
        return $this->options;
    }

    public function merge(SocketOptionsInterface $other): self
    {
        $merged = array_merge($this->options, $other->toArray());
        return new self($merged);
    }
}
