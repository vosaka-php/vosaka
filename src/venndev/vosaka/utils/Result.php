<?php

declare(strict_types=1);

namespace venndev\vosaka\utils;

use Generator;
use RuntimeException;
use Throwable;

final class Result
{
    /** @var callable[] */
    private array $callbacks = [];

    public function __construct(public readonly Generator $task)
    {
        // TODO: Implement the logic for handling the task.
    }

    public function map(callable $callback): Result
    {
        $this->callbacks[] = $callback;
        return $this;
    }

    private function executeCallbacks(mixed $result): Generator
    {
        foreach ($this->callbacks as $callback) {
            try {
                $result = $callback($result);
                if ($result instanceof Generator) {
                    $result = yield from $result;
                }
            } catch (Throwable $e) {
                return $e;
            }
        }
        return $result;
    }

    public function unwrap(): Generator
    {
        $result = yield from $this->task;
        $transformedResult = yield from $this->executeCallbacks($result);

        if ($transformedResult instanceof Throwable) {
            throw $transformedResult;
        }

        return $transformedResult;
    }

    public function unwrapOr(mixed $default): Generator
    {
        $result = yield from $this->task;
        $transformedResult = yield from $this->executeCallbacks($result);

        return $transformedResult instanceof Throwable ? $default : $transformedResult;
    }

    public function expect(string $message): Generator
    {
        $result = yield from $this->task;
        $transformedResult = yield from $this->executeCallbacks($result);

        if ($transformedResult instanceof Throwable) {
            throw new RuntimeException($message, 0, $transformedResult);
        }

        return $transformedResult;
    }

    public function __invoke(): Generator
    {
        $result = yield from $this->task;
        $transformedResult = yield from $this->executeCallbacks($result);

        return $transformedResult instanceof Throwable
            ? $transformedResult->getMessage()
            : $transformedResult;
    }
}