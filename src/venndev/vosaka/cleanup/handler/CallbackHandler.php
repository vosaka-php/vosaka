<?php

declare(strict_types=1);

namespace venndev\vosaka\cleanup\handler;

use Exception;
use venndev\vosaka\cleanup\logger\LoggerInterface;

/**
 * Handles cleanup callbacks
 */
final class CallbackHandler
{
    private array $cleanupCallbacks = [];
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function addCleanupCallback(callable $callback): self
    {
        $this->cleanupCallbacks[] = $callback;
        $this->logger->log('Added cleanup callback');
        return $this;
    }

    public function executeCallbacks(): void
    {
        foreach ($this->cleanupCallbacks as $callback) {
            try {
                call_user_func($callback);
                $this->logger->log('Executed cleanup callback');
            } catch (Exception $e) {
                $this->logger->log('Cleanup callback failed: '.$e->getMessage());
            }
        }
        $this->cleanupCallbacks = [];
    }

    public function getCount(): int
    {
        return count($this->cleanupCallbacks);
    }
}