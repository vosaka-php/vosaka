<?php

declare(strict_types=1);

namespace venndev\vosaka\cleanup;

use Exception;

final class GracefulShutdown
{
    private array $sockets = [];
    private array $tempFiles = [];
    private array $childPids = [];
    private array $cleanupCallbacks = [];
    private bool $isRegistered = false;
    private bool $isWindows;
    private bool $enableLogging;
    private string $stateFile;
    private string $logFile;

    public function __construct(string $stateFile = '/tmp/graceful_shutdown_state.json', string $logFile = '/tmp/graceful_shutdown.log', bool $enableLogging = false)
    {
        $this->isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        $this->stateFile = $stateFile;
        $this->logFile = $logFile;
        $this->enableLogging = $enableLogging;
        $this->registerCleanupHandlers();
        $this->cleanupPreviousState();
    }

    public function setStateFile(string $stateFile): self
    {
        $this->stateFile = $stateFile;
        return $this;
    }

    public function setLogFile(string $logFile): self
    {
        $this->logFile = $logFile;
        return $this;
    }

    /**
     * Register handlers for signals and shutdown
     */
    private function registerCleanupHandlers()
    {
        if ($this->isRegistered) {
            return;
        }

        // Register PHP's shutdown function for runtime errors
        register_shutdown_function([$this, 'handleFatalError']);

        if (!$this->isWindows) {
            // Unix signals for graceful termination
            if (function_exists('pcntl_async_signals')) {
                pcntl_async_signals(true);
                pcntl_signal(SIGINT, [$this, 'handleTermination']);  // Ctrl+C
                pcntl_signal(SIGTERM, [$this, 'handleTermination']); // kill
                pcntl_signal(SIGHUP, [$this, 'handleTermination']);  // Hangup
            }
        } else {
            // Windows Ctrl+C handling
            if (function_exists('sapi_windows_set_ctrl_handler')) {
                sapi_windows_set_ctrl_handler([$this, 'handleWindowsCtrlC']);
            }
        }

        $this->isRegistered = true;
    }

    /**
     * Cleanup resources from previous run (e.g., after SIGKILL)
     */
    private function cleanupPreviousState()
    {
        if (file_exists($this->stateFile)) {
            $state = json_decode(file_get_contents($this->stateFile), true);
            if (is_array($state)) {
                if (!empty($state['tempFiles'])) {
                    foreach ($state['tempFiles'] as $file) {
                        if (file_exists($file)) {
                            @unlink($file);
                            $this->log("Cleaned up previous temp file: $file");
                        }
                    }
                }

                if (!empty($state['sockets'])) {
                    $this->log("Previous sockets detected but cannot be closed: " . implode(', ', $state['sockets']));
                }

                if (!empty($state['childPids'])) {
                    $this->log("Previous child PIDs detected but cannot be terminated: " . implode(', ', $state['childPids']));
                }

                @unlink($this->stateFile);
            }
        }
    }

    /**
     * Save current state to file
     */
    private function saveState()
    {
        $state = [
            'sockets' => array_map(function ($socket) {
                return is_resource($socket) ? (string) $socket : 'invalid';
            }, $this->sockets),
            'tempFiles' => $this->tempFiles,
            'childPids' => $this->childPids,
        ];

        @file_put_contents($this->stateFile, json_encode($state, JSON_PRETTY_PRINT));
    }

    /**
     * Log a message to the log file if logging is enabled
     */
    private function log(string $message)
    {
        if ($this->enableLogging) {
            $timestamp = date('Y-m-d H:i:s');
            @file_put_contents($this->logFile, "[$timestamp] $message\n", FILE_APPEND);
        }
    }

    /**
     * Add a socket to be closed during cleanup
     */
    public function addSocket($socket)
    {
        if (is_resource($socket)) {
            $this->sockets[] = $socket;
            $this->saveState();
            $this->log("Added socket: " . (string) $socket);
        }

        return $this;
    }

    /**
     * Add a temporary file to be deleted during cleanup
     */
    public function addTempFile(string $filePath)
    {
        if (file_exists($filePath)) {
            $this->tempFiles[] = $filePath;
            $this->saveState();
            $this->log("Added temp file: $filePath");
        }

        return $this;
    }

    /**
     * Add a child process PID to be terminated during cleanup
     */
    public function addChildProcess(int $pid)
    {
        if ($pid > 0 && !$this->isWindows) {
            $this->childPids[] = $pid;
            $this->saveState();
            $this->log("Added child process PID: $pid");
        }

        return $this;
    }

    /**
     * Add a callback to be executed during cleanup
     */
    public function addCleanupCallback(callable $callback)
    {
        $this->cleanupCallbacks[] = $callback;
        $this->log("Added cleanup callback");
        return $this;
    }

    /**
     * Remove invalid sockets from the list
     */
    private function pruneInvalidSockets()
    {
        $validSockets = [];

        foreach ($this->sockets as $socket) {
            if (is_resource($socket)) {
                $validSockets[] = $socket;
            } else {
                $this->log("Removed invalid socket reference: " . (string) $socket);
            }
        }

        if (count($validSockets) !== count($this->sockets)) {
            $this->sockets = $validSockets;
            $this->saveState();
        }
    }

    /**
     * Handle termination signals (Unix)
     */
    public function handleTermination($signal)
    {
        $this->log("Received termination signal: $signal");
        switch ($signal) {
            case SIGINT:
            case SIGTERM:
            case SIGHUP:
                $this->performCleanup();
                exit(0);
        }
    }

    /**
     * Handle Windows Ctrl+C
     */
    public function handleWindowsCtrlC($event)
    {
        if ($event === PHP_WINDOWS_EVENT_CTRL_C) {
            $this->log("Received Windows Ctrl+C");
            $this->performCleanup();
            exit(0);
        }
    }

    /**
     * Handle fatal PHP errors
     */
    public function handleFatalError()
    {
        $error = error_get_last();

        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $this->log("Fatal error detected: {$error['message']} at {$error['file']}:{$error['line']}");
            $this->performCleanup();
        }
    }

    /**
     * Perform cleanup of resources
     */
    private function performCleanup()
    {
        $this->log("Starting cleanup");

        // Prune invalid sockets before cleanup
        $this->pruneInvalidSockets();

        // Close all sockets
        foreach ($this->sockets as $socket) {
            if (is_resource($socket)) {
                @stream_socket_shutdown($socket, STREAM_SHUT_RDWR);
                @fclose($socket);
                $this->log("Closed socket: " . (string) $socket);
            }
        }

        $this->sockets = [];

        // Terminate child processes (Unix only)
        if (!$this->isWindows) {
            $status = null;
            foreach ($this->childPids as $pid) {
                if (posix_kill($pid, 0)) { // Check if process exists
                    posix_kill($pid, SIGTERM);
                    $this->log("Sent SIGTERM to child process PID: $pid");
                    // Optionally wait for child to exit
                    pcntl_waitpid($pid, $status, WNOHANG);
                }
            }
        }

        $this->childPids = [];

        // Delete temporary files
        foreach ($this->tempFiles as $file) {
            if (file_exists($file)) {
                @unlink($file);
                $this->log("Deleted temp file: $file");
            }
        }

        $this->tempFiles = [];

        // Execute custom cleanup callbacks
        foreach ($this->cleanupCallbacks as $callback) {
            try {
                call_user_func($callback);
                $this->log("Executed cleanup callback");
            } catch (Exception $e) {
                $this->log("Cleanup callback failed: " . $e->getMessage());
            }
        }

        $this->cleanupCallbacks = [];

        // Remove state file after cleanup
        if (file_exists($this->stateFile)) {
            @unlink($this->stateFile);
            $this->log("Removed state file: {$this->stateFile}");
        }
    }

    /**
     * Manually trigger cleanup
     */
    public function cleanup()
    {
        $this->log("Manual cleanup triggered");
        $this->performCleanup();
    }

    /**
     * Enable or disable logging
     */
    public function setLogging(bool $enableLogging)
    {
        $this->enableLogging = $enableLogging;
        $this->log("Logging " . ($enableLogging ? "enabled" : "disabled"));
    }

    /**
     * Destructor to ensure cleanup
     */
    public function __destruct()
    {
        $this->performCleanup();
    }
}