<?php

declare(strict_types=1);

namespace venndev\vosaka\cleanup\handler;

use venndev\vosaka\cleanup\interfaces\CleanupHandlerInterface;
use venndev\vosaka\cleanup\logger\LoggerInterface;

/**
 * Handles socket resource cleanup
 */
final class SocketCleanupHandler implements CleanupHandlerInterface
{
    private array $sockets = [];
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function addSocket(mixed $socket): self
    {
        if (is_resource($socket)) {
            $id = $this->getResourceId($socket);
            $this->sockets[$id] = [
                "resource" => $socket,
                "id" => $id,
                "added_at" => time(),
                "type" => get_resource_type($socket),
            ];
            $this->logger->log("Added socket: $id");
        }
        return $this;
    }

    public function removeSocket(mixed $socket): void
    {
        $id = $this->getResourceId($socket);
        if (isset($this->sockets[$id])) {
            unset($this->sockets[$id]);
            $this->logger->log("Removed socket from array: $id");
        }
    }

    public function cleanup(): void
    {
        foreach ($this->sockets as $id => $socketData) {
            if (!is_resource($socketData["resource"])) {
                $this->removeSocket($id);
                $this->logger->log("Removed invalid socket: $id");
            }
        }
    }

    public function cleanupAll(): void
    {
        foreach ($this->sockets as $id => $socketData) {
            if (is_resource($socketData["resource"])) {
                @stream_socket_shutdown(
                    $socketData["resource"],
                    STREAM_SHUT_RDWR
                );
                @fclose($socketData["resource"]);
                $this->logger->log("Closed socket: $id");
            }
            $this->removeSocket($id);
        }
    }

    public function getResourceCount(): int
    {
        $count = 0;
        foreach ($this->sockets as $socketData) {
            if (is_resource($socketData["resource"])) {
                $count++;
            }
        }
        return $count;
    }

    public function getSocketIds(): array
    {
        $socketIds = [];
        foreach ($this->sockets as $socketData) {
            if (is_resource($socketData["resource"])) {
                $socketIds[] = $socketData["id"];
            }
        }
        return $socketIds;
    }

    private function getResourceId(mixed $resource): string
    {
        return (string) $resource;
    }
}
