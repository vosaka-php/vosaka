<?php

declare(strict_types=1);

namespace venndev\vosaka\breaker;

use Generator;
use RuntimeException;
use Throwable;
use venndev\vosaka\core\Result;
use venndev\vosaka\core\Future;

/**
 * Circuit Breaker implementation to prevent cascading failures in distributed systems.
 * It allows a certain number of failures before opening the circuit and preventing further calls.
 */
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

    public function call(Generator $task): Result
    {
        $fn = function () use ($task): Generator {
            if (! $this->allow()) {
                throw new RuntimeException(
                    "Circuit breaker is open, cannot execute task"
                );
            }

            try {
                yield $task;
            } catch (Throwable $e) {
                $this->recordFailure();
                throw $e; // Re-throw the exception after recording the failure
            }
        };

        return Future::new($fn());
    }
}
