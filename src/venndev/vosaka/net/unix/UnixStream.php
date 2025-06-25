<?php

declare(strict_types=1);

namespace venndev\vosaka\net\unix;

use Generator;
use InvalidArgumentException;
use venndev\vosaka\core\Result;
use venndev\vosaka\VOsaka;

final class UnixStream
{
    public function __construct(
        private mixed $socket,
        private readonly string $path
    ) {
        stream_set_blocking($this->socket, false);
    }

    /**
     * Read data from the stream
     */
    public function read(int $length): Result
    {
        if (!$this->socket) {
            throw new InvalidArgumentException("Socket is closed");
        }

        $readTask = function () use ($length): Generator {
            $data = yield @fread($this->socket, $length);

            if ($data === false) {
                $error = error_get_last();
                throw new InvalidArgumentException("Read failed: " . ($error['message'] ?? 'Unknown error'));
            }

            return $data;
        };

        return VOsaka::spawn($readTask());
    }

    /**
     * Write data to the stream
     */
    public function write(string $data): Result
    {
        if (!$this->socket) {
            throw new InvalidArgumentException("Socket is closed");
        }

        $writeTask = function () use ($data): Generator {
            $result = yield @fwrite($this->socket, $data);

            if ($result === false) {
                $error = error_get_last();
                throw new InvalidArgumentException("Write failed: " . ($error['message'] ?? 'Unknown error'));
            }

            return $result;
        };

        return VOsaka::spawn($writeTask());
    }

    public function getPeerPath(): string
    {
        $name = stream_socket_get_name($this->socket, true);
        return $name ?: '';
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
    }

    public function isClosed(): bool
    {
        return !$this->socket;
    }
}