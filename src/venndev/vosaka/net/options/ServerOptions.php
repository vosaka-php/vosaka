<?php

declare(strict_types=1);

namespace venndev\vosaka\net\options;

/**
 * Server-specific options
 */
class ServerOptions extends SocketOptions
{
    public function __construct()
    {
        parent::__construct();
        $this->options['backlog'] = SOMAXCONN;
    }

    public function setBacklog(int $backlog): self
    {
        $this->options['backlog'] = $backlog;
        return $this;
    }
}
