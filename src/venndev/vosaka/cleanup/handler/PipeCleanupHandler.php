<?php

declare(strict_types=1);

namespace vennDev\vosaka\cleanup;

use venndev\vosaka\cleanup\logger\LoggerInterface;

/**
 * Handles pipe resource cleanup
 */
final class PipeCleanupHandler implements CleanupHandlerInterface
{
    private array $pipes = [];
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function addPipe(mixed $pipe): self
    {
        if (is_resource($pipe)) {
            $id = $this->getResourceId($pipe);
            $this->pipes[$id] = [
                'resource' => $pipe,
                'id' => $id,
                'added_at' => time(),
                'type' => get_resource_type($pipe),
            ];
            $this->logger->log("Added pipe: $id");
        }
        return $this;
    }

    public function addPipes(array $pipes): self
    {
        foreach ($pipes as $pipe) {
            $this->addPipe($pipe);
        }
        return $this;
    }

    public function removePipe(mixed $pipe): void
    {
        $id = $this->getResourceId($pipe);
        if (isset($this->pipes[$id])) {
            unset($this->pipes[$id]);
            $this->logger->log("Removed pipe from array: $id");
        }
    }

    public function cleanup(): void
    {
        foreach ($this->pipes as $id => $pipeData) {
            if (! is_resource($pipeData['resource'])) {
                $this->removePipe($id);
                $this->logger->log("Removed invalid pipe: $id");
            }
        }
    }

    public function cleanupAll(): void
    {
        foreach ($this->pipes as $id => $pipeData) {
            if (is_resource($pipeData['resource'])) {
                @fclose($pipeData['resource']);
                $this->logger->log("Closed pipe: $id");
            }
            $this->removePipe($id);
        }
    }

    public function getResourceCount(): int
    {
        $count = 0;
        foreach ($this->pipes as $pipeData) {
            if (is_resource($pipeData['resource'])) {
                $count++;
            }
        }
        return $count;
    }

    public function getPipeIds(): array
    {
        $pipeIds = [];
        foreach ($this->pipes as $pipeData) {
            if (is_resource($pipeData['resource'])) {
                $pipeIds[] = $pipeData['id'];
            }
        }
        return $pipeIds;
    }

    private function getResourceId(mixed $resource): string
    {
        return (string) $resource;
    }
}