<?php

declare(strict_types=1);

namespace venndev\vosaka\net\unix;

use Generator;
use InvalidArgumentException;
use Throwable;
use venndev\vosaka\io\Await;
use venndev\vosaka\time\Sleep;
use venndev\vosaka\utils\Defer;
use venndev\vosaka\VOsaka;

final class UnixListener
{
    private mixed $socket = null;
    private bool $isListening = false;

    public function __construct(
        private readonly mixed $socketResource,
        private readonly string $path
    ) {
        $this->socket = $socketResource;
        $this->isListening = true;
        stream_set_blocking($this->socket, false);

        yield from Defer::c(function () {
            $this->close();
        });
    }

    /**
     * Accept incoming connections
     */
    public function accept(): Generator
    {
        if (!$this->isListening) {
            throw new InvalidArgumentException("Listener is not bound");
        }

        $acceptTask = function (): Generator {
            while (true) {
                $clientSocket = @stream_socket_accept($this->socket, 0, $peerName);

                if ($clientSocket) {
                    stream_set_blocking($clientSocket, false);
                    return new UnixStream($clientSocket, $peerName ?: 'unix:unknown');
                }

                yield Sleep::c(0.001);
            }
        };

        return yield from VOsaka::spawn($acceptTask())->unwrap();
    }

    /**
     * Get incoming connections as async iterator
     */
    public function incoming(): Generator
    {
        while ($this->isListening) {
            try {
                $stream = yield from $this->accept();
                yield $stream;
            } catch (Throwable $e) {
                error_log("Accept error: " . $e->getMessage());
                yield Sleep::c(0.1);
            }
        }
    }

    public function getLocalPath(): string
    {
        return $this->path;
    }

    public function close(): void
    {
        if ($this->socket) {
            @fclose($this->socket);
            $this->socket = null;
        }
        $this->isListening = false;
    }

    public function isClosed(): bool
    {
        return !$this->isListening;
    }
}