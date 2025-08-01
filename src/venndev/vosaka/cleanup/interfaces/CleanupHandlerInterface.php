<?php

declare(strict_types=1);

namespace venndev\vosaka\cleanup\interfaces;

/**
 * Interface for cleanup handlers
 */
interface CleanupHandlerInterface
{
    public function cleanup(): void;
    public function cleanupAll(): void;
    public function getResourceCount(): int;
}
