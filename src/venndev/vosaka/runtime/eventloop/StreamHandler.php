<?php

declare(strict_types=1);

namespace venndev\vosaka\runtime\eventloop;

use Throwable;
use BadMethodCallException;

/**
 * StreamHandler class for handling stream I/O operations and signals.
 *
 * This class is responsible for managing read/write streams and signal handling.
 */
final class StreamHandler
{
    // Stream handling properties
    private array $readStreams = [];
    private array $readListeners = [];
    private array $writeStreams = [];
    private array $writeListeners = [];

    // Signal handling
    private bool $pcntl = false;
    private bool $pcntlPoll = false;
    private array $signals = [];

    public function __construct()
    {
        // Initialize signal handling
        $this->pcntl =
            function_exists("pcntl_signal") &&
            function_exists("pcntl_signal_dispatch");
        $this->pcntlPoll =
            $this->pcntl && !function_exists("pcntl_async_signals");

        // Prefer async signals if available (PHP 7.1+)
        if ($this->pcntl && !$this->pcntlPoll) {
            pcntl_async_signals(true);
        }
    }

    /**
     * Add a read stream to the handler
     */
    public function addReadStream($stream, callable $listener): void
    {
        $key = (int) $stream;

        if (!isset($this->readStreams[$key])) {
            $this->readStreams[$key] = $stream;
            $this->readListeners[$key] = $listener;
        }
    }

    /**
     * Add a write stream to the handler
     */
    public function addWriteStream($stream, callable $listener): void
    {
        $key = (int) $stream;

        if (!isset($this->writeStreams[$key])) {
            $this->writeStreams[$key] = $stream;
            $this->writeListeners[$key] = $listener;
        }
    }

    /**
     * Remove a read stream from the handler
     */
    public function removeReadStream($stream): void
    {
        $key = (int) $stream;

        unset($this->readStreams[$key], $this->readListeners[$key]);
    }

    /**
     * Remove a write stream from the handler
     */
    public function removeWriteStream($stream): void
    {
        $key = (int) $stream;

        unset($this->writeStreams[$key], $this->writeListeners[$key]);
    }

    /**
     * Add signal handler
     */
    public function addSignal(int $signal, callable $listener): void
    {
        if ($this->pcntl === false) {
            throw new BadMethodCallException(
                'Event loop feature "signals" isn\'t supported'
            );
        }

        if (!isset($this->signals[$signal])) {
            $this->signals[$signal] = [];
            pcntl_signal($signal, [$this, "handleSignal"]);
        }

        $this->signals[$signal][] = $listener;
    }

    /**
     * Remove signal handler
     */
    public function removeSignal(int $signal, callable $listener): void
    {
        if (!isset($this->signals[$signal])) {
            return;
        }

        $key = array_search($listener, $this->signals[$signal], true);
        if ($key !== false) {
            unset($this->signals[$signal][$key]);
        }

        if (empty($this->signals[$signal])) {
            unset($this->signals[$signal]);
            pcntl_signal($signal, SIG_DFL);
        }
    }

    /**
     * Handle signal internally
     */
    public function handleSignal(int $signal): void
    {
        if (isset($this->signals[$signal])) {
            foreach ($this->signals[$signal] as $listener) {
                $listener($signal);
            }
        }
    }

    /**
     * Wait for stream activity
     */
    public function waitForStreamActivity(?int $timeout): void
    {
        $read = $this->readStreams;
        $write = $this->writeStreams;

        $available = $this->streamSelect($read, $write, $timeout);

        if ($this->pcntlPoll) {
            pcntl_signal_dispatch();
        }

        if (false === $available) {
            // System call interrupted
            return;
        }

        // Handle readable streams
        foreach ($read as $stream) {
            $key = (int) $stream;
            if (isset($this->readListeners[$key])) {
                try {
                    $this->readListeners[$key]($stream);
                } catch (Throwable $e) {
                    error_log(
                        "Read stream callback error: " . $e->getMessage()
                    );
                }
            }
        }

        // Handle writable streams
        foreach ($write as $stream) {
            $key = (int) $stream;
            if (isset($this->writeListeners[$key])) {
                try {
                    $this->writeListeners[$key]($stream);
                } catch (Throwable $e) {
                    error_log(
                        "Write stream callback error: " . $e->getMessage()
                    );
                }
            }
        }
    }

    /**
     * Stream select implementation with Windows compatibility
     */
    private function streamSelect(
        array &$read,
        array &$write,
        ?int $timeout
    ): int|false {
        if ($read || $write) {
            // Windows compatibility for connection attempts
            $except = null;
            if (DIRECTORY_SEPARATOR === "") {
                $except = [];
                foreach ($write as $key => $socket) {
                    if (!isset($read[$key]) && @ftell($socket) === 0) {
                        $except[$key] = $socket;
                    }
                }
            }

            $previous = set_error_handler(function ($errno, $errstr) use (
                &$previous
            ) {
                // Suppress EINTR warnings
                $eintr = defined("SOCKET_EINTR")
                    ? SOCKET_EINTR
                    : (defined("PCNTL_EINTR")
                        ? PCNTL_EINTR
                        : 4);
                if (
                    $errno === E_WARNING &&
                    strpos($errstr, "[" . $eintr . "]: ") !== false
                ) {
                    return;
                }
                return $previous !== null
                    ? call_user_func_array($previous, func_get_args())
                    : false;
            });

            try {
                $ret = stream_select(
                    $read,
                    $write,
                    $except,
                    $timeout === null ? null : 0,
                    $timeout
                );
                restore_error_handler();
            } catch (Throwable $e) {
                restore_error_handler();
                throw $e;
            }

            if ($except) {
                $write = array_merge($write, $except);
            }
            return $ret;
        }

        // No streams to monitor, just sleep if timeout specified
        if ($timeout > 0) {
            usleep($timeout);
        } elseif ($timeout === null) {
            sleep(PHP_INT_MAX);
        }

        return 0;
    }

    /**
     * Check if handler has streams
     */
    public function hasStreams(): bool
    {
        return !empty($this->readStreams) || !empty($this->writeStreams);
    }

    /**
     * Check if handler has signals
     */
    public function hasSignals(): bool
    {
        return !empty($this->signals);
    }

    /**
     * Close and clean up all streams and signals
     */
    public function close(): void
    {
        $this->readStreams = [];
        $this->readListeners = [];
        $this->writeStreams = [];
        $this->writeListeners = [];
        $this->signals = [];
    }

    /**
     * Get statistics about streams and signals
     */
    public function getStats(): array
    {
        return [
            "read_streams" => count($this->readStreams),
            "write_streams" => count($this->writeStreams),
            "signal_handlers" => count($this->signals),
        ];
    }
}
