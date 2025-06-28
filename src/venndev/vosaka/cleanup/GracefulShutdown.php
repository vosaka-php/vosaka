<?php

declare(strict_types=1);

namespace venndev\vosaka\cleanup;

use Exception;
use venndev\vosaka\core\Constants;

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

    public function __construct(
        string $stateFile = "/tmp/graceful_shutdown_state.json",
        string $logFile = "/tmp/graceful_shutdown.log",
        bool $enableLogging = false
    ) {
        $this->isWindows = strtoupper(substr(PHP_OS, 0, 3)) === "WIN";
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

        register_shutdown_function([$this, "handleFatalError"]);

        if (!$this->isWindows) {
            if (function_exists("pcntl_async_signals")) {
                pcntl_async_signals(true);
                pcntl_signal(
                    Constants::getSafeSignal("SIGINT") ?? Constants::SIGINT,
                    [$this, "handleTermination"]
                );
                pcntl_signal(
                    Constants::getSafeSignal("SIGTERM") ?? Constants::SIGTERM,
                    [$this, "handleTermination"]
                );
                pcntl_signal(
                    Constants::getSafeSignal("SIGHUP") ?? Constants::SIGHUP,
                    [$this, "handleTermination"]
                );
            }
        } else {
            if (function_exists("sapi_windows_set_ctrl_handler")) {
                sapi_windows_set_ctrl_handler([$this, "handleWindowsCtrlC"]);
            }
        }

        $this->isRegistered = true;
    }

    private function cleanupPreviousState()
    {
        if (file_exists($this->stateFile)) {
            $state = json_decode(file_get_contents($this->stateFile), true);
            if (is_array($state)) {
                if (!empty($state["tempFiles"])) {
                    foreach ($state["tempFiles"] as $file) {
                        if (file_exists($file)) {
                            @unlink($file);
                            $this->log("Cleaned up previous temp file: $file");
                        }
                    }
                }

                if (!empty($state["sockets"])) {
                    $this->log(
                        "Previous sockets detected but cannot be closed: " .
                            implode(", ", $state["sockets"])
                    );
                }

                if (!empty($state["childPids"])) {
                    $this->log(
                        "Previous child PIDs detected but cannot be terminated: " .
                            implode(", ", $state["childPids"])
                    );
                }

                if (!empty($state["pipes"])) {
                    $this->log(
                        "Previous pipes detected but cannot be closed: " .
                            implode(", ", $state["pipes"])
                    );
                }

                if (!empty($state["processes"])) {
                    $this->log(
                        "Previous processes detected but cannot be closed: " .
                            implode(", ", $state["processes"])
                    );
                }

                @unlink($this->stateFile);
            }
        }
    }

    private function saveState()
    {
        $socketIds = [];
        foreach ($this->sockets as $socketData) {
            if (is_resource($socketData["resource"])) {
                $socketIds[] = $socketData["id"];
            }
        }

        $pipeIds = [];
        foreach ($this->pipes as $pipeData) {
            if (is_resource($pipeData["resource"])) {
                $pipeIds[] = $pipeData["id"];
            }
        }

        $processIds = [];
        foreach ($this->processes as $processData) {
            if (is_resource($processData["resource"])) {
                $processIds[] = $processData["id"];
            }
        }

        $state = [
            "sockets" => $socketIds,
            "tempFiles" => $this->tempFiles,
            "childPids" => $this->childPids,
            "pipes" => $pipeIds,
            "processes" => $processIds,
        ];

        @file_put_contents(
            $this->stateFile,
            json_encode($state, JSON_PRETTY_PRINT)
        );
    }

    private function log(string $message)
    {
        if ($this->enableLogging) {
            $timestamp = date("Y-m-d H:i:s");
            @file_put_contents(
                $this->logFile,
                "[$timestamp] $message\n",
                FILE_APPEND
            );
        }
    }

    private function getResourceId(mixed $resource): string
    {
        return (string) $resource;
    }

    public function addSocket(mixed $socket)
    {
        if (is_resource($socket)) {
            $id = $this->getResourceId($socket);
            $this->sockets[$id] = [
                "resource" => $socket,
                "id" => $id,
                "added_at" => time(),
                "type" => get_resource_type($socket),
            ];
            $this->saveState();
            $this->log("Added socket: $id");
        }

        return $this;
    }

    public function addTempFile(string $filePath)
    {
        if (file_exists($filePath)) {
            $this->tempFiles[$filePath] = $filePath;
            $this->saveState();
            $this->log("Added temp file: $filePath");
        }

        return $this;
    }

    public function addChildProcess(int $pid)
    {
        if ($pid > 0 && !$this->isWindows) {
            $this->childPids[$pid] = $pid;
            $this->saveState();
            $this->log("Added child process PID: $pid");
        }

        return $this;
    }

    public function addPipe(mixed $pipe)
    {
        if (is_resource($pipe)) {
            $id = $this->getResourceId($pipe);
            $this->pipes[$id] = [
                "resource" => $pipe,
                "id" => $id,
                "added_at" => time(),
                "type" => get_resource_type($pipe),
            ];
            $this->saveState();
            $this->log("Added pipe: $id");
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

    public function addProcess(mixed $process)
    {
        if (is_resource($process)) {
            $id = $this->getResourceId($process);
            $this->processes[$id] = [
                "resource" => $process,
                "id" => $id,
                "added_at" => time(),
                "type" => get_resource_type($process),
            ];
            $this->saveState();
            $this->log("Added process: $id");
        }

        return $this;
    }

    public function addProcOpen(mixed $process, array $pipes = [])
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

    public function removeSocket(mixed $socket)
    {
        $id = $this->getResourceId($socket);
        if (isset($this->sockets[$id])) {
            unset($this->sockets[$id]);
            $this->log("Removed socket from array: $id");
        }
    }

    public function removePipe(mixed $pipe)
    {
        $id = $this->getResourceId($pipe);
        if (isset($this->pipes[$id])) {
            unset($this->pipes[$id]);
            $this->log("Removed pipe from array: $id");
        }
    }

    public function removeProcess(mixed $process)
    {
        $id = $this->getResourceId($process);
        if (isset($this->processes[$id])) {
            unset($this->processes[$id]);
            $this->log("Removed process from array: $id");
        }
    }

    public function removeTempFile(string $path)
    {
        if (isset($this->tempFiles[$path])) {
            unset($this->tempFiles[$path]);
            $this->log("Removed temp file from array: $path");
        }
    }

    public function removeChildProcessPid(string $pid)
    {
        if (isset($this->childPids[$pid])) {
            unset($this->childPids[$pid]);
            $this->log("Removed child process pid from array: $pid");
        }
    }

    public function handleTermination(mixed $signal)
    {
        $this->log("Received termination signal: $signal");
        $sigint = Constants::getSafeSignal("SIGINT") ?? Constants::SIGINT;
        $sigterm = Constants::getSafeSignal("SIGTERM") ?? Constants::SIGTERM;
        $sighup = Constants::getSafeSignal("SIGHUP") ?? Constants::SIGHUP;

        switch ($signal) {
            case $sigint:
            case $sigterm:
            case $sighup:
                $this->performCleanup();
                exit(0);
        }
    }

    public function handleWindowsCtrlC(mixed $event)
    {
        $ctrlc =
            Constants::getSafeWindowsEvent("PHP_WINDOWS_EVENT_CTRL_C") ??
            Constants::PHP_WINDOWS_EVENT_CTRL_C;
        if ($event === $ctrlc) {
            $this->log("Received Windows Ctrl+C");
            $this->performCleanup();
            exit(0);
        }
    }

    public function handleFatalError()
    {
        $error = error_get_last();

        if (
            $error !== null &&
            in_array($error["type"], Constants::FATAL_ERROR_TYPES)
        ) {
            $this->log(
                "Fatal error detected: {$error["message"]} at {$error["file"]}:{$error["line"]}"
            );
            $this->performCleanup();
        }
    }

    private function performCleanup(bool $justInvalid = false)
    {
        $this->log("Starting cleanup");

        if ($justInvalid) {
            // Cleanup invalid resources only
            $this->cleanupInvalidResources();
            $this->log("Cleanup completed (removed invalid resources only)");
            return;
        }

        // Cleanup pipes
        foreach ($this->pipes as $id => $pipeData) {
            if (is_resource($pipeData["resource"])) {
                @fclose($pipeData["resource"]);
                $this->log("Closed pipe: $id");
            }
            $this->removePipe($id);
        }

        // Cleanup processes
        foreach ($this->processes as $id => $processData) {
            if (is_resource($processData["resource"])) {
                $sigterm =
                    Constants::getSafeSignal("SIGTERM") ?? Constants::SIGTERM;
                @proc_terminate($processData["resource"], $sigterm);
                @proc_close($processData["resource"]);
                $this->log("Terminated and closed process: $id");
            }
            $this->removeProcess($id);
        }

        // Cleanup sockets
        foreach ($this->sockets as $id => $socketData) {
            if (is_resource($socketData["resource"])) {
                @stream_socket_shutdown(
                    $socketData["resource"],
                    STREAM_SHUT_RDWR
                );
                @fclose($socketData["resource"]);
                $this->log("Closed socket: $id");
            }
            $this->removeSocket($id);
        }

        // Cleanup child processes
        if (!$this->isWindows && !empty($this->childPids)) {
            $status = null;
            $sigterm =
                Constants::getSafeSignal("SIGTERM") ?? Constants::SIGTERM;
            $wnohang = Constants::getWaitFlag("WNOHANG");
            foreach ($this->childPids as $pid) {
                if (function_exists("posix_kill") && posix_kill($pid, 0)) {
                    posix_kill($pid, $sigterm);
                    $this->log("Sent SIGTERM to child process PID: $pid");
                    if (function_exists("pcntl_waitpid")) {
                        pcntl_waitpid($pid, $status, $wnohang);
                    }
                }
            }
        }
        $this->childPids = [];

        // Cleanup temp files
        foreach ($this->tempFiles as $file) {
            if (file_exists($file)) {
                @unlink($file);
                $this->log("Deleted temp file: $file");
            }
        }
        $this->tempFiles = [];

        // Execute cleanup callbacks
        foreach ($this->cleanupCallbacks as $callback) {
            try {
                call_user_func($callback);
                $this->log("Executed cleanup callback");
            } catch (Exception $e) {
                $this->log("Cleanup callback failed: " . $e->getMessage());
            }
        }
        $this->cleanupCallbacks = [];

        // Remove state file
        if (file_exists($this->stateFile)) {
            @unlink($this->stateFile);
            $this->log("Removed state file: {$this->stateFile}");
        }
    }

    private function cleanupInvalidResources()
    {
        // Remove invalid sockets
        foreach ($this->sockets as $id => $socketData) {
            if (!is_resource($socketData["resource"])) {
                $this->removeSocket($id);
                $this->log("Removed invalid socket: $id");
            }
        }

        // Remove invalid pipes
        foreach ($this->pipes as $id => $pipeData) {
            if (!is_resource($pipeData["resource"])) {
                $this->removePipe($id);
                $this->log("Removed invalid pipe: $id");
            }
        }

        // Remove invalid processes
        foreach ($this->processes as $id => $processData) {
            if (!is_resource($processData["resource"])) {
                $this->removeProcess($id);
                $this->log("Removed invalid process: $id");
            }
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

    /**
     * Get count of tracked resources
     */
    public function getResourceCounts(): array
    {
        $socketCount = 0;
        foreach ($this->sockets as $socketData) {
            if (is_resource($socketData["resource"])) {
                $socketCount++;
            }
        }

        $pipeCount = 0;
        foreach ($this->pipes as $pipeData) {
            if (is_resource($pipeData["resource"])) {
                $pipeCount++;
            }
        }

        $processCount = 0;
        foreach ($this->processes as $processData) {
            if (is_resource($processData["resource"])) {
                $processCount++;
            }
        }

        return [
            "sockets" => $socketCount,
            "pipes" => $pipeCount,
            "processes" => $processCount,
            "temp_files" => count($this->tempFiles),
            "child_pids" => count($this->childPids),
            "callbacks" => count($this->cleanupCallbacks),
        ];
    }

    public function __destruct()
    {
        $this->performCleanup();
    }
}
