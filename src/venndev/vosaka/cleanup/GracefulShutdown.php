<?php

declare(strict_types=1);

namespace venndev\vosaka\cleanup;

use venndev\vosaka\cleanup\handler\CallbackHandler;
use venndev\vosaka\cleanup\handler\ChildProcessHandler;
use venndev\vosaka\cleanup\handler\ProcessCleanupHandler;
use venndev\vosaka\cleanup\handler\StateManager;
use venndev\vosaka\cleanup\handler\TempFileHandler;
use venndev\vosaka\cleanup\logger\FileLogger;
use venndev\vosaka\core\Constants;

/**
 * Main graceful shutdown orchestrator
 */
final class GracefulShutdown
{
    private SocketCleanupHandler $socketHandler;
    private PipeCleanupHandler $pipeHandler;
    private ProcessCleanupHandler $processHandler;
    private ChildProcessHandler $childProcessHandler;
    private TempFileHandler $tempFileHandler;
    private CallbackHandler $callbackHandler;
    private StateManager $stateManager;
    private FileLogger $logger;
    private bool $isRegistered = false;
    private bool $isWindows;

    public function __construct(
        string $stateFile = '/tmp/graceful_shutdown_state.json',
        string $logFile = '/tmp/graceful_shutdown.log',
        bool $enableLogging = false
    ) {
        $this->isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

        $this->logger = new FileLogger($logFile, $enableLogging);
        $this->stateManager = new StateManager($stateFile, $this->logger);

        $this->socketHandler = new SocketCleanupHandler($this->logger);
        $this->pipeHandler = new PipeCleanupHandler($this->logger);
        $this->processHandler = new ProcessCleanupHandler($this->logger);
        $this->childProcessHandler = new ChildProcessHandler($this->logger, $this->isWindows);
        $this->tempFileHandler = new TempFileHandler($this->logger);
        $this->callbackHandler = new CallbackHandler($this->logger);

        $this->registerCleanupHandlers();
        $this->stateManager->cleanupPreviousState();
    }

    public function setStateFile(string $stateFile): self
    {
        $this->stateManager->setStateFile($stateFile);
        return $this;
    }

    public function setLogFile(string $logFile): self
    {
        $this->logger->setLogFile($logFile);
        return $this;
    }

    public function setLogging(bool $enableLogging): self
    {
        $this->logger->setLogging($enableLogging);
        return $this;
    }

    // Resource management methods
    public function addSocket(mixed $socket): self
    {
        $this->socketHandler->addSocket($socket);
        $this->saveCurrentState();
        return $this;
    }

    public function addTempFile(string $filePath): self
    {
        $this->tempFileHandler->addTempFile($filePath);
        $this->saveCurrentState();
        return $this;
    }

    public function addChildProcess(int $pid): self
    {
        $this->childProcessHandler->addChildProcess($pid);
        $this->saveCurrentState();
        return $this;
    }

    public function addPipe(mixed $pipe): self
    {
        $this->pipeHandler->addPipe($pipe);
        $this->saveCurrentState();
        return $this;
    }

    public function addPipes(array $pipes): self
    {
        $this->pipeHandler->addPipes($pipes);
        $this->saveCurrentState();
        return $this;
    }

    public function addProcess(mixed $process): self
    {
        $this->processHandler->addProcess($process);
        $this->saveCurrentState();
        return $this;
    }

    public function addProcOpen(mixed $process, array $pipes = []): self
    {
        $this->processHandler->addProcess($process);
        $this->pipeHandler->addPipes($pipes);
        $this->saveCurrentState();
        return $this;
    }

    public function addCleanupCallback(callable $callback): self
    {
        $this->callbackHandler->addCleanupCallback($callback);
        return $this;
    }

    // Remove methods
    public function removeSocket(mixed $socket): self
    {
        $this->socketHandler->removeSocket($socket);
        return $this;
    }

    public function removePipe(mixed $pipe): self
    {
        $this->pipeHandler->removePipe($pipe);
        return $this;
    }

    public function removeProcess(mixed $process): self
    {
        $this->processHandler->removeProcess($process);
        return $this;
    }

    public function removeTempFile(string $path): self
    {
        $this->tempFileHandler->removeTempFile($path);
        return $this;
    }

    public function removeChildProcessPid(string $pid): self
    {
        $this->childProcessHandler->removeChildProcessPid($pid);
        return $this;
    }

    // Cleanup methods
    public function cleanup(): void
    {
        $this->logger->log('Lite cleanup triggered');
        $this->socketHandler->cleanup();
        $this->pipeHandler->cleanup();
        $this->processHandler->cleanup();
    }

    public function cleanupAll(): void
    {
        $this->logger->log('Full cleanup triggered');
        $this->performCleanup();
    }

    public function getResourceCounts(): array
    {
        return [
            'sockets' => $this->socketHandler->getResourceCount(),
            'pipes' => $this->pipeHandler->getResourceCount(),
            'processes' => $this->processHandler->getResourceCount(),
            'temp_files' => $this->tempFileHandler->getCount(),
            'child_pids' => $this->childProcessHandler->getCount(),
            'callbacks' => $this->callbackHandler->getCount(),
        ];
    }

    // Signal handlers
    public function handleTermination(mixed $signal): void
    {
        $this->logger->log("Received termination signal: $signal");
        $sigint = Constants::getSafeSignal('SIGINT') ?? Constants::SIGINT;
        $sigterm = Constants::getSafeSignal('SIGTERM') ?? Constants::SIGTERM;
        $sighup = Constants::getSafeSignal('SIGHUP') ?? Constants::SIGHUP;

        switch ($signal) {
            case $sigint:
            case $sigterm:
            case $sighup:
                $this->performCleanup();
                exit(0);
        }
    }

    public function handleWindowsCtrlC(mixed $event): void
    {
        $ctrlc = Constants::getSafeWindowsEvent('PHP_WINDOWS_EVENT_CTRL_C') ??
            Constants::PHP_WINDOWS_EVENT_CTRL_C;
        if ($event === $ctrlc) {
            $this->logger->log('Received Windows Ctrl+C');
            $this->performCleanup();
            exit(0);
        }
    }

    public function handleFatalError(): void
    {
        $error = error_get_last();

        if ($error !== null && in_array($error['type'], Constants::FATAL_ERROR_TYPES)) {
            $this->logger->log(
                "Fatal error detected: {$error['message']} at {$error['file']}:{$error['line']}"
            );
            $this->performCleanup();
        }
    }

    private function registerCleanupHandlers(): void
    {
        if ($this->isRegistered) {
            return;
        }

        register_shutdown_function([$this, 'handleFatalError']);

        if (! $this->isWindows) {
            if (function_exists('pcntl_async_signals')) {
                pcntl_async_signals(true);
                pcntl_signal(
                    Constants::getSafeSignal('SIGINT') ?? Constants::SIGINT,
                    [$this, 'handleTermination']
                );
                pcntl_signal(
                    Constants::getSafeSignal('SIGTERM') ?? Constants::SIGTERM,
                    [$this, 'handleTermination']
                );
                pcntl_signal(
                    Constants::getSafeSignal('SIGHUP') ?? Constants::SIGHUP,
                    [$this, 'handleTermination']
                );
            }
        } else {
            if (function_exists('sapi_windows_set_ctrl_handler')) {
                sapi_windows_set_ctrl_handler([$this, 'handleWindowsCtrlC']);
            }
        }

        $this->isRegistered = true;
    }

    private function performCleanup(): void
    {
        $this->logger->log('Starting cleanup');

        $this->pipeHandler->cleanupAll();
        $this->processHandler->cleanupAll();
        $this->socketHandler->cleanupAll();
        $this->childProcessHandler->cleanupAll();
        $this->tempFileHandler->cleanupAll();
        $this->callbackHandler->executeCallbacks();
        $this->stateManager->removeStateFile();

        $this->logger->log('Cleanup completed');
    }

    private function saveCurrentState(): void
    {
        $state = [
            'sockets' => $this->socketHandler->getSocketIds(),
            'tempFiles' => $this->tempFileHandler->getTempFiles(),
            'childPids' => $this->childProcessHandler->getChildPids(),
            'pipes' => $this->pipeHandler->getPipeIds(),
            'processes' => $this->processHandler->getProcessIds(),
        ];

        $this->stateManager->saveState($state);
    }

    public function __destruct()
    {
        $this->performCleanup();
    }
}