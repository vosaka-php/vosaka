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

    /**
     * Factory method to create a new instance of CBreaker.
     *
     * @param int $threshold The number of failures before the circuit opens.
     * @param int $timeout The time in seconds after which the circuit resets.
     * @return CBreaker
     */
    public static function new(int $threshold, int $timeout): CBreaker
    {
        return new self($threshold, $timeout);
    }

    /**
     * Checks if the circuit breaker allows the execution of a task.
     *
     * @return bool True if the task can be executed, false if the circuit is open.
     */
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

    /**
     * Records a failure in the circuit breaker.
     * This increments the failure count and updates the last failure time.
     */
    public function recordFailure(): void
    {
        $this->failureCount++;
        $this->lastFailureTime = time();
    }

    /**
     * Resets the circuit breaker, clearing the failure count and last failure time.
     */
    public function reset(): void
    {
        $this->failureCount = 0;
        $this->lastFailureTime = 0;
    }

    /**
     * Calls a task and manages the circuit breaker state.
     * If the circuit is open, it throws an exception.
     * If the task fails, it records the failure.
     *
     * @param Generator $task The task to be executed.
     * @return Result The result of the task execution.
     * @throws RuntimeException if the circuit breaker is open.
     */
    public function call(Generator $task): Result
    {
        $fn = function () use ($task): Generator {
            if (!$this->allow()) {
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
