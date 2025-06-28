<?php

declare(strict_types=1);

namespace venndev\vosaka\net\unix;

use Generator;
use InvalidArgumentException;
use venndev\vosaka\time\Sleep;
use venndev\vosaka\core\Result;
use venndev\vosaka\VOsaka;

final class UnixStream
{
    private bool $isClosed = false;
    private int $bufferSize = 8192;

    public function __construct(
        private mixed $socket,
        private readonly string $path
    ) {
        VOsaka::getLoop()->getGracefulShutdown()->addSocket($socket);
    }

    /**
     * Read data from stream
     * @param int|null $maxBytes Maximum bytes to read, null for default buffer size
     * @return Result<string|null> Data read from stream, or null if closed
     */
    public function read(int|null $maxBytes = null): Result
    {
        $fn = function () use ($maxBytes): Generator {
            if ($this->isClosed) {
                throw new InvalidArgumentException("Stream is closed");
            }

            $maxBytes ??= $this->bufferSize;

            while (true) {
                $data = @fread($this->socket, $maxBytes);

                if ($data === false || ($data === "" && feof($this->socket))) {
                    return null; // Connection closed
                }

                if ($data !== "") {
                    return $data;
                }

                yield Sleep::c(0.001); // Non-blocking wait
            }
        };

        return VOsaka::spawn($fn());
    }

    /**
     * Read exact number of bytes
     * @param int $bytes Number of bytes to read
     * @return Result<string> Data read from stream
     */
    public function readExact(int $bytes): Result
    {
        $fn = function () use ($bytes): Generator {
            $buffer = "";
            $remaining = $bytes;

            while ($remaining > 0) {
                $chunk = yield from $this->read($remaining)->unwrap();

                if ($chunk === null) {
                    throw new InvalidArgumentException(
                        "Connection closed before reading exact bytes"
                    );
                }

                $buffer .= $chunk;
                $remaining -= strlen($chunk);
            }

            return $buffer;
        };

        return VOsaka::spawn($fn());
    }

    /**
     * Read until delimiter
     * @param string $delimiter Delimiter to read until
     * @return Result<string|null> Data read until delimiter, or null if closed
     */
    public function readUntil(string $delimiter): Result
    {
        $fn = function () use ($delimiter): Generator {
            $buffer = "";

            while (true) {
                $chunk = yield from $this->read(1)->unwrap();

                if ($chunk === null) {
                    return $buffer ?: null;
                }

                $buffer .= $chunk;

                if (str_ends_with($buffer, $delimiter)) {
                    return substr($buffer, 0, -strlen($delimiter));
                }
            }
        };

        return VOsaka::spawn($fn());
    }

    /**
     * Read line (until \n)
     * @return Result<string|null> Line read from stream, or null if closed
     */
    public function readLine(): Result
    {
        return $this->readUntil("\n");
    }

    /**
     * Write data to stream
     * @param string $data Data to write
     * @return Result<int> Number of bytes written
     */
    public function write(string $data): Result
    {
        $fn = function () use ($data): Generator {
            if ($this->isClosed) {
                throw new InvalidArgumentException("Stream is closed");
            }

            $totalBytes = strlen($data);
            $bytesWritten = 0;

            while ($bytesWritten < $totalBytes) {
                $result = @fwrite($this->socket, substr($data, $bytesWritten));

                if ($result === false) {
                    throw new InvalidArgumentException("Write failed");
                }

                $bytesWritten += $result;

                if ($bytesWritten < $totalBytes) {
                    yield Sleep::c(0.001);
                }
            }

            return $bytesWritten;
        };

        return VOsaka::spawn($fn());
    }

    /**
     * Write all data (ensures complete write)
     * @param string $data Data to write
     * @return Result<int> Number of bytes written
     */
    public function writeAll(string $data): Result
    {
        return $this->write($data);
    }

    /**
     * Flush the stream
     * @return Result<void>
     */
    public function flush(): Result
    {
        $fn = function (): Generator {
            if ($this->socket) {
                @fflush($this->socket);
            }
            yield Sleep::c(0.001);
        };

        return VOsaka::spawn($fn());
    }

    /**
     * Get peer path
     */
    public function peerPath(): string
    {
        if (!$this->socket) {
            return "";
        }

        $name = stream_socket_get_name($this->socket, true);
        return $name ?: "";
    }

    /**
     * Get local path
     */
    public function localPath(): string
    {
        return $this->path;
    }

    /**
     * Close the stream
     */
    public function close(): void
    {
        if ($this->socket && !$this->isClosed) {
            VOsaka::getLoop()
                ->getGracefulShutdown()
                ->removeSocket($this->socket);
            @fclose($this->socket);
            $this->socket = null;
            $this->isClosed = true;
        }
    }

    public function isClosed(): bool
    {
        return $this->isClosed;
    }

    /**
     * Split stream into reader and writer
     */
    public function split(): array
    {
        return [new UnixReadHalf($this), new UnixWriteHalf($this)];
    }
}
