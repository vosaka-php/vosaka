<?php

declare(strict_types=1);

namespace venndev\vosaka\cleanup\handler;

use venndev\vosaka\cleanup\logger\LoggerInterface;
use venndev\vosaka\core\Constants;

/**
 * Handles child process PID cleanup
 */
final class ChildProcessHandler
{
    private array $childPids = [];
    private LoggerInterface $logger;
    private bool $isWindows;

    public function __construct(LoggerInterface $logger, bool $isWindows)
    {
        $this->logger = $logger;
        $this->isWindows = $isWindows;
    }

    public function addChildProcess(int $pid): self
    {
        if ($pid > 0 && ! $this->isWindows) {
            $this->childPids[$pid] = $pid;
            $this->logger->log("Added child process PID: $pid");
        }
        return $this;
    }

    public function removeChildProcessPid(string $pid): void
    {
        if (isset($this->childPids[$pid])) {
            unset($this->childPids[$pid]);
            $this->logger->log("Removed child process pid from array: $pid");
        }
    }

    public function cleanupAll(): void
    {
        if (! $this->isWindows && ! empty($this->childPids)) {
            $status = null;
            $sigterm = Constants::getSafeSignal('SIGTERM') ?? Constants::SIGTERM;
            $wnohang = Constants::getWaitFlag('WNOHANG');

            foreach ($this->childPids as $pid) {
                if (function_exists('posix_kill') && posix_kill($pid, 0)) {
                    posix_kill($pid, $sigterm);
                    $this->logger->log("Sent SIGTERM to child process PID: $pid");
                    if (function_exists('pcntl_waitpid')) {
                        pcntl_waitpid($pid, $status, $wnohang);
                    }
                }
            }
        }
        $this->childPids = [];
    }

    public function getChildPids(): array
    {
        return $this->childPids;
    }

    public function getCount(): int
    {
        return count($this->childPids);
    }
}