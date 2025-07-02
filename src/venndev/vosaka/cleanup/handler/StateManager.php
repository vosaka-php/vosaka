<?php

declare(strict_types=1);

namespace venndev\vosaka\cleanup\handler;

use venndev\vosaka\cleanup\logger\LoggerInterface;

/**
 * Handles state persistence
 */
final class StateManager
{
    private string $stateFile;
    private LoggerInterface $logger;

    public function __construct(string $stateFile, LoggerInterface $logger)
    {
        $this->stateFile = $stateFile;
        $this->logger = $logger;
    }

    public function saveState(array $state): void
    {
        @file_put_contents(
            $this->stateFile,
            json_encode($state, JSON_PRETTY_PRINT)
        );
    }

    public function loadState(): ?array
    {
        if (file_exists($this->stateFile)) {
            $state = json_decode(file_get_contents($this->stateFile), true);
            return is_array($state) ? $state : null;
        }
        return null;
    }

    public function cleanupPreviousState(): void
    {
        $state = $this->loadState();
        if ($state === null) {
            return;
        }

        if (! empty($state['tempFiles'])) {
            foreach ($state['tempFiles'] as $file) {
                if (file_exists($file)) {
                    @unlink($file);
                    $this->logger->log("Cleaned up previous temp file: $file");
                }
            }
        }

        $this->logPreviousResources('sockets', $state);
        $this->logPreviousResources('childPids', $state);
        $this->logPreviousResources('pipes', $state);
        $this->logPreviousResources('processes', $state);

        $this->removeStateFile();
    }

    public function removeStateFile(): void
    {
        if (file_exists($this->stateFile)) {
            @unlink($this->stateFile);
            $this->logger->log("Removed state file: {$this->stateFile}");
        }
    }

    public function setStateFile(string $stateFile): void
    {
        $this->stateFile = $stateFile;
    }

    private function logPreviousResources(string $type, array $state): void
    {
        if (! empty($state[$type])) {
            $message = match ($type) {
                'sockets' => 'Previous sockets detected but cannot be closed: ',
                'childPids' => 'Previous child PIDs detected but cannot be terminated: ',
                'pipes' => 'Previous pipes detected but cannot be closed: ',
                'processes' => 'Previous processes detected but cannot be closed: ',
                default => "Previous $type detected: "
            };
            $this->logger->log($message.implode(', ', $state[$type]));
        }
    }
}