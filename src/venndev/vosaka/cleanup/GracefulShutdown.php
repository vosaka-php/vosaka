<?php

declare(strict_types=1);

namespace venndev\vosaka\cleanup;

use Exception;

final class GracefulShutdown
{
    private array $sockets = [];
    private array $tempFiles = [];
    private array $childPids = [];
    private array $pipes = [];
    private array $processes = [];
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

    private function registerCleanupHandlers()
    {
        if ($this->isRegistered) {
            return;
        }

        register_shutdown_function([$this, 'handleFatalError']);

        if (!$this->isWindows) {
            if (function_exists('pcntl_async_signals')) {
                pcntl_async_signals(true);
                pcntl_signal(SIGINT, [$this, 'handleTermination']);
                pcntl_signal(SIGTERM, [$this, 'handleTermination']);
                pcntl_signal(SIGHUP, [$this, 'handleTermination']);
            }
        } else {
            if (function_exists('sapi_windows_set_ctrl_handler')) {
                sapi_windows_set_ctrl_handler([$this, 'handleWindowsCtrlC']);
            }
        }

        $this->isRegistered = true;
    }

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

                if (!empty($state['pipes'])) {
                    $this->log("Previous pipes detected but cannot be closed: " . implode(', ', $state['pipes']));
                }

                if (!empty($state['processes'])) {
                    $this->log("Previous processes detected but cannot be closed: " . implode(', ', $state['processes']));
                }

                @unlink($this->stateFile);
            }
        }
    }

    private function saveState()
    {
        $state = [
            'sockets' => array_map(function ($socket) {
                return is_resource($socket) ? (string) $socket : 'invalid';
            }, $this->sockets),
            'tempFiles' => $this->tempFiles,
            'childPids' => $this->childPids,
            'pipes' => array_map(function ($pipe) {
                return is_resource($pipe) ? (string) $pipe : 'invalid';
            }, $this->pipes),
            'processes' => array_map(function ($process) {
                return is_resource($process) ? (string) $process : 'invalid';
            }, $this->processes),
        ];

        @file_put_contents($this->stateFile, json_encode($state, JSON_PRETTY_PRINT));
    }

    private function log(string $message)
    {
        if ($this->enableLogging) {
            $timestamp = date('Y-m-d H:i:s');
            @file_put_contents($this->logFile, "[$timestamp] $message\n", FILE_APPEND);
        }
    }

    public function addSocket($socket)
    {
        if (is_resource($socket)) {
            $this->sockets[] = $socket;
            $this->saveState();
            $this->log("Added socket: " . (string) $socket);
        }

        return $this;
    }

    public function addTempFile(string $filePath)
    {
        if (file_exists($filePath)) {
            $this->tempFiles[] = $filePath;
            $this->saveState();
            $this->log("Added temp file: $filePath");
        }

        return $this;
    }

    public function addChildProcess(int $pid)
    {
        if ($pid > 0 && !$this->isWindows) {
            $this->childPids[] = $pid;
            $this->saveState();
            $this->log("Added child process PID: $pid");
        }

        return $this;
    }

    public function addPipe($pipe)
    {
        if (is_resource($pipe)) {
            $this->pipes[] = $pipe;
            $this->saveState();
            $this->log("Added pipe: " . (string) $pipe);
        }

        return $this;
    }

    public function addPipes(array $pipes)
    {
        foreach ($pipes as $pipe) {
            $this->addPipe($pipe);
        }

        return $this;
    }

    public function addProcess($process)
    {
        if (is_resource($process)) {
            $this->processes[] = $process;
            $this->saveState();
            $this->log("Added process: " . (string) $process);
        }

        return $this;
    }

    public function addProcOpen($process, array $pipes = [])
    {
        $this->addProcess($process);
        $this->addPipes($pipes);

        return $this;
    }

    public function addCleanupCallback(callable $callback)
    {
        $this->cleanupCallbacks[] = $callback;
        $this->log("Added cleanup callback");
        return $this;
    }

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

    private function pruneInvalidPipes()
    {
        $validPipes = [];

        foreach ($this->pipes as $pipe) {
            if (is_resource($pipe)) {
                $validPipes[] = $pipe;
            } else {
                $this->log("Removed invalid pipe reference: " . (string) $pipe);
            }
        }

        if (count($validPipes) !== count($this->pipes)) {
            $this->pipes = $validPipes;
            $this->saveState();
        }
    }

    private function pruneInvalidProcesses()
    {
        $validProcesses = [];

        foreach ($this->processes as $process) {
            if (is_resource($process)) {
                $validProcesses[] = $process;
            } else {
                $this->log("Removed invalid process reference: " . (string) $process);
            }
        }

        if (count($validProcesses) !== count($this->processes)) {
            $this->processes = $validProcesses;
            $this->saveState();
        }
    }

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

    public function handleWindowsCtrlC($event)
    {
        if ($event === PHP_WINDOWS_EVENT_CTRL_C) {
            $this->log("Received Windows Ctrl+C");
            $this->performCleanup();
            exit(0);
        }
    }

    public function handleFatalError()
    {
        $error = error_get_last();

        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $this->log("Fatal error detected: {$error['message']} at {$error['file']}:{$error['line']}");
            $this->performCleanup();
        }
    }

    private function performCleanup(bool $justInvalid = false)
    {
        $this->log("Starting cleanup");

        $this->pruneInvalidSockets();
        $this->pruneInvalidPipes();
        $this->pruneInvalidProcesses();

        if ($justInvalid) {
            $this->log("Cleanup completed (just invalid resources)");
            return;
        }

        foreach ($this->pipes as $pipe) {
            if (is_resource($pipe)) {
                @fclose($pipe);
                $this->log("Closed pipe: " . (string) $pipe);
            }
        }
        $this->pipes = [];

        foreach ($this->processes as $process) {
            if (is_resource($process)) {
                @proc_terminate($process, SIGTERM);
                @proc_close($process);
                $this->log("Terminated and closed process: " . (string) $process);
            }
        }
        $this->processes = [];

        foreach ($this->sockets as $socket) {
            if (is_resource($socket)) {
                @stream_socket_shutdown($socket, STREAM_SHUT_RDWR);
                @fclose($socket);
                $this->log("Closed socket: " . (string) $socket);
            }
        }
        $this->sockets = [];

        if (!$this->isWindows && !empty($this->childPids)) {
            $status = null;
            foreach ($this->childPids as $pid) {
                if (posix_kill($pid, 0)) {
                    posix_kill($pid, SIGTERM);
                    $this->log("Sent SIGTERM to child process PID: $pid");
                    pcntl_waitpid($pid, $status, WNOHANG);
                }
            }
        }
        $this->childPids = [];

        foreach ($this->tempFiles as $file) {
            if (file_exists($file)) {
                @unlink($file);
                $this->log("Deleted temp file: $file");
            }
        }
        $this->tempFiles = [];

        foreach ($this->cleanupCallbacks as $callback) {
            try {
                call_user_func($callback);
                $this->log("Executed cleanup callback");
            } catch (Exception $e) {
                $this->log("Cleanup callback failed: " . $e->getMessage());
            }
        }
        $this->cleanupCallbacks = [];

        if (file_exists($this->stateFile)) {
            @unlink($this->stateFile);
            $this->log("Removed state file: {$this->stateFile}");
        }
    }

    public function cleanup()
    {
        $this->log("Lite cleanup triggered");
        $this->performCleanup(true);
    }

    public function cleanupAll()
    {
        $this->log("Full cleanup triggered");
        $this->performCleanup(false);
    }

    public function setLogging(bool $enableLogging)
    {
        $this->enableLogging = $enableLogging;
        $this->log("Logging " . ($enableLogging ? "enabled" : "disabled"));
    }

    public function __destruct()
    {
        $this->performCleanup();
    }
}