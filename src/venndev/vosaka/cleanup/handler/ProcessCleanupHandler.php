<?php

declare(strict_types=1);

namespace venndev\vosaka\cleanup\handler;

use venndev\vosaka\cleanup\interfaces\CleanupHandlerInterface;
use venndev\vosaka\cleanup\logger\LoggerInterface;
use venndev\vosaka\core\Constants;

/**
 * Handles process resource cleanup
 */
final class ProcessCleanupHandler implements CleanupHandlerInterface
{
    private array $processes = [];
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function addProcess(mixed $process): self
    {
        if (is_resource($process)) {
            $id = $this->getResourceId($process);
            $this->processes[$id] = [
                "resource" => $process,
                "id" => $id,
                "added_at" => time(),
                "type" => get_resource_type($process),
            ];
            $this->logger->log("Added process: $id");
        }
        return $this;
    }

    public function removeProcess(mixed $process): void
    {
        $id = $this->getResourceId($process);
        if (isset($this->processes[$id])) {
            unset($this->processes[$id]);
            $this->logger->log("Removed process from array: $id");
        }
    }

    public function cleanup(): void
    {
        foreach ($this->processes as $id => $processData) {
            if (!is_resource($processData["resource"])) {
                $this->removeProcess($id);
                $this->logger->log("Removed invalid process: $id");
            }
        }
    }

    public function cleanupAll(): void
    {
        foreach ($this->processes as $id => $processData) {
            if (is_resource($processData["resource"])) {
                $sigterm =
                    Constants::getSafeSignal("SIGTERM") ?? Constants::SIGTERM;
                @proc_terminate($processData["resource"], $sigterm);
                @proc_close($processData["resource"]);
                $this->logger->log("Terminated and closed process: $id");
            }
            $this->removeProcess($id);
        }
    }

    public function getResourceCount(): int
    {
        $count = 0;
        foreach ($this->processes as $processData) {
            if (is_resource($processData["resource"])) {
                $count++;
            }
        }
        return $count;
    }

    public function getProcessIds(): array
    {
        $processIds = [];
        foreach ($this->processes as $processData) {
            if (is_resource($processData["resource"])) {
                $processIds[] = $processData["id"];
            }
        }
        return $processIds;
    }

    private function getResourceId(mixed $resource): string
    {
        return (string) $resource;
    }
}
