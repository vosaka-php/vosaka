<?php

declare(strict_types=1);

namespace venndev\vosaka\net\options;

/**
 * Socket options configuration
 */
class SocketOptions
{
    public array $options = [
        'reuseaddr' => true,
        'reuseport' => false,
        'nodelay' => false,
        'keepalive' => false,
        'linger' => null,
        'sndbuf' => null,
        'rcvbuf' => null,
        'timeout' => 30.0,
        'ssl' => false,
        'verify_peer' => true,
        'verify_peer_name' => true,
        'allow_self_signed' => false,
        'ssl_cert' => null,
        'ssl_key' => null,
        'ssl_ca' => null,
    ];

    public function setReuseAddr(bool $enable): self
    {
        $this->options['reuseaddr'] = $enable;
        return $this;
    }

    public function setReusePort(bool $enable): self
    {
        $this->options['reuseport'] = $enable;
        return $this;
    }

    public function setNoDelay(bool $enable): self
    {
        $this->options['nodelay'] = $enable;
        return $this;
    }

    public function setKeepAlive(bool $enable): self
    {
        $this->options['keepalive'] = $enable;
        return $this;
    }

    public function setLinger(bool|int $linger): self
    {
        $this->options['linger'] = $linger;
        return $this;
    }

    public function setSendBufferSize(int $size): self
    {
        $this->options['sndbuf'] = $size;
        return $this;
    }

    public function setReceiveBufferSize(int $size): self
    {
        $this->options['rcvbuf'] = $size;
        return $this;
    }

    public function setTimeout(float $seconds): self
    {
        $this->options['timeout'] = $seconds;
        return $this;
    }

    public function enableSsl(bool $enable = true): self
    {
        $this->options['ssl'] = $enable;
        return $this;
    }

    public function setSslCertificate(string $path): self
    {
        $this->options['ssl_cert'] = $path;
        return $this;
    }

    public function setSslKey(string $path): self
    {
        $this->options['ssl_key'] = $path;
        return $this;
    }

    public function setSslCa(string $path): self
    {
        $this->options['ssl_ca'] = $path;
        return $this;
    }

    public function setVerifyPeer(bool $verify): self
    {
        $this->options['verify_peer'] = $verify;
        $this->options['verify_peer_name'] = $verify;
        return $this;
    }

    public function setAllowSelfSigned(bool $allow): self
    {
        $this->options['allow_self_signed'] = $allow;
        return $this;
    }

    public function toArray(): array
    {
        return array_filter($this->options, fn($v) => $v !== null);
    }

    public static function create(): self
    {
        return new self();
    }
}
