<?php

declare(strict_types=1);

namespace venndev\vosaka\net\unix;

use Generator;
use InvalidArgumentException;
use Throwable;
use venndev\vosaka\time\Sleep;
use venndev\vosaka\core\Result;
use venndev\vosaka\core\Future;
use venndev\vosaka\VOsaka;

final class UnixStream
{
    private bool $isClosed = false;
    private int $bufferSize = 8192;
    private array $options = [];

    public function __construct(
        private mixed $socket,
        private readonly string $path,
        array $options = []
    ) {
        $this->options = array_merge(
            [
                "buffer_size" => 8192,
                "read_timeout" => 30,
                "write_timeout" => 30,
                "keepalive" => true,
                "linger" => false,
                "sndbuf" => 65536,
                "rcvbuf" => 65536,
            ],
            $options
        );

        $this->bufferSize = $this->options["buffer_size"];

        VOsaka::getLoop()->getGracefulShutdown()->addSocket($socket);
        $this->applySocketOptions();
    }

    private function applySocketOptions(): void
    {
        if (! $this->socket) {
            return;
        }

        try {
            if (function_exists("socket_import_stream")) {
                $sock = socket_import_stream($this->socket);
                if ($sock === false) {
                    return;
                }

                // Buffer sizes
                if ($this->options["sndbuf"] > 0) {
                    socket_set_option(
                        $sock,
                        SOL_SOCKET,
                        SO_SNDBUF,
                        $this->options["sndbuf"]
                    );
                }
                if ($this->options["rcvbuf"] > 0) {
                    socket_set_option(
                        $sock,
                        SOL_SOCKET,
                        SO_RCVBUF,
                        $this->options["rcvbuf"]
                    );
                }

                // SO_LINGER control
                if ($this->options["linger"] === false) {
                    $linger = ["l_onoff" => 1, "l_linger" => 0];
                    socket_set_option($sock, SOL_SOCKET, SO_LINGER, $linger);
                }

                // Keep alive for Unix sockets (if supported)
                if ($this->options["keepalive"]) {
                    socket_set_option($sock, SOL_SOCKET, SO_KEEPALIVE, 1);
                }
            }
        } catch (Throwable $e) {
            error_log(
                "Warning: Could not set Unix stream options: ".
                $e->getMessage()
            );
        }
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
            $startTime = time();

            while (true) {
                // Check for timeout
                if (time() - $startTime > $this->options["read_timeout"]) {
                    throw new InvalidArgumentException("Read timeout exceeded");
                }

                $data = @fread($this->socket, $maxBytes);

                if ($data === false) {
                    // Check if socket is still valid
                    if (! is_resource($this->socket) || feof($this->socket)) {
                        return null; // Connection closed
                    }
                    throw new InvalidArgumentException("Read failed");
                }

                if ($data === "" && feof($this->socket)) {
                    return null; // Connection closed
                }

                if ($data !== "") {
                    return $data;
                }

                yield Sleep::new(0.001); // Non-blocking wait
            }
        };

        return Future::new($fn());
    }

    /**
     * Read exact number of bytes
     * @param int $bytes Number of bytes to read
     * @return Result<string> Data read from stream
     */
    public function readExact(int $bytes): Result
    {
        $fn = function () use ($bytes): Generator {
            if ($bytes <= 0) {
                throw new InvalidArgumentException(
                    "Bytes must be greater than 0"
                );
            }

            $buffer = "";
            $remaining = $bytes;
            $startTime = time();

            while ($remaining > 0) {
                // Check for timeout
                if (time() - $startTime > $this->options["read_timeout"]) {
                    throw new InvalidArgumentException("Read timeout exceeded");
                }

                $chunk = yield from $this->read(
                    min($remaining, $this->bufferSize)
                )->unwrap();

                if ($chunk === null) {
                    throw new InvalidArgumentException(
                        "Connection closed before reading exact bytes (got ".
                        strlen($buffer).
                        " of {$bytes} bytes)"
                    );
                }

                $buffer .= $chunk;
                $remaining -= strlen($chunk);
            }

            return $buffer;
        };

        return Future::new($fn());
    }

    /**
     * Read until delimiter
     * @param string $delimiter Delimiter to read until
     * @return Result<string|null> Data read until delimiter, or null if closed
     */
    public function readUntil(string $delimiter): Result
    {
        $fn = function () use ($delimiter): Generator {
            if (empty($delimiter)) {
                throw new InvalidArgumentException("Delimiter cannot be empty");
            }

            $buffer = "";
            $delimiterLength = strlen($delimiter);
            $startTime = time();

            while (true) {
                // Check for timeout
                if (time() - $startTime > $this->options["read_timeout"]) {
                    throw new InvalidArgumentException("Read timeout exceeded");
                }

                $chunk = yield from $this->read(1)->unwrap();

                if ($chunk === null) {
                    return $buffer ?: null;
                }

                $buffer .= $chunk;

                // Check if we have the delimiter
                if (strlen($buffer) >= $delimiterLength) {
                    if (substr($buffer, -$delimiterLength) === $delimiter) {
                        return substr($buffer, 0, -$delimiterLength);
                    }
                }

                // Prevent buffer from growing too large
                if (strlen($buffer) > 1048576) {
                    // 1MB limit
                    throw new InvalidArgumentException(
                        "Buffer size exceeded while reading until delimiter"
                    );
                }
            }
        };

        return Future::new($fn());
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

            if (empty($data)) {
                return 0;
            }

            $totalBytes = strlen($data);
            $bytesWritten = 0;
            $startTime = time();

            while ($bytesWritten < $totalBytes) {
                // Check for timeout
                if (time() - $startTime > $this->options["write_timeout"]) {
                    throw new InvalidArgumentException(
                        "Write timeout exceeded"
                    );
                }

                $chunk = substr($data, $bytesWritten);
                $result = @fwrite($this->socket, $chunk);

                if ($result === false) {
                    if (! is_resource($this->socket) || feof($this->socket)) {
                        throw new InvalidArgumentException(
                            "Connection closed during write"
                        );
                    }
                    throw new InvalidArgumentException("Write failed");
                }

                if ($result === 0) {
                    // Socket buffer might be full, wait a bit
                    yield Sleep::new(0.001);
                    continue;
                }

                $bytesWritten += $result;

                if ($bytesWritten < $totalBytes) {
                    yield Sleep::new(0.001);
                }
            }

            return $bytesWritten;
        };

        return Future::new($fn());
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
            if ($this->isClosed) {
                throw new InvalidArgumentException("Stream is closed");
            }

            if ($this->socket && is_resource($this->socket)) {
                if (! @fflush($this->socket)) {
                    throw new InvalidArgumentException("Flush failed");
                }
            }
            yield Sleep::new(0.001);
        };

        return Future::new($fn());
    }

    /**
     * Get peer path
     */
    public function peerPath(): string
    {
        if (! $this->socket || $this->isClosed) {
            return "";
        }

        try {
            $name = stream_socket_get_name($this->socket, true);
            return $name ?: "";
        } catch (Throwable $e) {
            return "";
        }
    }

    /**
     * Get local path
     */
    public function localPath(): string
    {
        return $this->path;
    }

    /**
     * Get stream options
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Set buffer size
     */
    public function setBufferSize(int $size): self
    {
        if ($size <= 0) {
            throw new InvalidArgumentException(
                "Buffer size must be greater than 0"
            );
        }
        $this->bufferSize = $size;
        $this->options["buffer_size"] = $size;
        return $this;
    }

    /**
     * Set read timeout
     */
    public function setReadTimeout(int $seconds): self
    {
        if ($seconds <= 0) {
            throw new InvalidArgumentException(
                "Timeout must be greater than 0"
            );
        }
        $this->options["read_timeout"] = $seconds;
        return $this;
    }

    /**
     * Set write timeout
     */
    public function setWriteTimeout(int $seconds): self
    {
        if ($seconds <= 0) {
            throw new InvalidArgumentException(
                "Timeout must be greater than 0"
            );
        }
        $this->options["write_timeout"] = $seconds;
        return $this;
    }

    /**
     * Close the stream
     */
    public function close(): void
    {
        if ($this->socket && ! $this->isClosed) {
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
        return $this->isClosed || ! is_resource($this->socket);
    }

    /**
     * Split stream into reader and writer
     */
    public function split(): array
    {
        return [new UnixReadHalf($this), new UnixWriteHalf($this)];
    }
}
