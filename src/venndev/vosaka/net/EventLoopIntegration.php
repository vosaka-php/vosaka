<?php

declare(strict_types=1);

namespace venndev\vosaka\net;

use venndev\vosaka\VOsaka;

/**
 * Event loop integration for sockets
 */
class EventLoopIntegration
{
    private array $readHandlers = [];
    private array $writeHandlers = [];
    private array $errorHandlers = [];

    /**
     * Register read handler
     */
    public function onReadable($socket, callable $handler): void
    {
        if (!is_resource($socket)) {
            return;
        }

        $id = (int) $socket;
        $this->readHandlers[$id] = $handler;

        VOsaka::getLoop()->addReadStream($socket, function () use ($handler, $socket) {
            $handler($socket);
        });
    }

    /**
     * Register write handler
     */
    public function onWritable($socket, callable $handler): void
    {
        if (!is_resource($socket)) {
            return;
        }

        $id = (int) $socket;
        $this->writeHandlers[$id] = $handler;

        VOsaka::getLoop()->addWriteStream($socket, function () use ($handler, $socket) {
            $handler($socket);
        });
    }

    /**
     * Remove read handler
     */
    public function removeReadable($socket): void
    {
        if (!is_resource($socket)) {
            return;
        }

        $id = (int) $socket;
        unset($this->readHandlers[$id]);

        VOsaka::getLoop()->removeReadStream($socket);
    }

    /**
     * Remove write handler
     */
    public function removeWritable($socket): void
    {
        if (!is_resource($socket)) {
            return;
        }

        $id = (int) $socket;
        unset($this->writeHandlers[$id]);

        VOsaka::getLoop()->removeWriteStream($socket);
    }

    /**
     * Remove all handlers
     */
    public function removeAll($socket): void
    {
        $this->removeReadable($socket);
        $this->removeWritable($socket);
    }
}
