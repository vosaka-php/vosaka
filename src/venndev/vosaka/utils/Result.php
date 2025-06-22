<?php

declare(strict_types=1);

namespace venndev\vosaka\utils;

use Generator;
use RuntimeException;
use Throwable;

final class Result
{
    public function __construct(public readonly Generator $task)
    {
        // TODO: Implement the logic for handling the result of the task.  
    }

    public function unwrap(): Generator
    {
        yield from $this->task;
        $result = $this->task->getReturn();
        if ($result instanceof Throwable) {
            throw $result;
        }
        return $result;
    }

    public function unwrapOr(mixed $default): Generator
    {
        yield from $this->task;
        $result = $this->task->getReturn();
        if ($result instanceof Throwable) {
            return $default;
        }
        return $result;
    }

    public function expect(string $message): Generator
    {
        yield from $this->task;
        $result = $this->task->getReturn();
        if ($result instanceof Throwable) {
            throw new RuntimeException($message, 0, $result);
        }
        return $result;
    }

    public function __invoke(): Generator
    {
        yield from $this->task;
        $result = $this->task->getReturn();
        if ($result instanceof Throwable) {
            return $result->getMessage();
        }
        return $result;
    }
}