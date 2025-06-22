<?php

declare(strict_types=1);

namespace venndev\vosaka\breaker;

use Generator;
use RuntimeException;
use Throwable;

final class CBreaker
{
    private int $failureCount = 0;
    private int $lastFailureTime = 0;
    private int $threshold;
    private int $timeout;

    public function __construct(int $threshold, int $timeout)
    {
        $this->threshold = $threshold;
        $this->timeout = $timeout;
    }

    public function allow(): bool
    {
        if ($this->failureCount >= $this->threshold) {
            if (time() - $this->lastFailureTime < $this->timeout) {
                return false;
            }
            $this->reset();
        }
        return true;
    }

    public function recordFailure(): void
    {
        $this->failureCount++;
        $this->lastFailureTime = time();
    }

    public function reset(): void
    {
        $this->failureCount = 0;
        $this->lastFailureTime = 0;
    }

    public function call(Generator $task): Generator
    {
        if (!$this->allow()) {
            throw new RuntimeException("Circuit breaker is open, cannot execute task");
        }

        try {
            yield $task;
        } catch (Throwable $e) {
            $this->recordFailure();
            throw $e; // Re-throw the exception after recording the failure
        }
    }
}