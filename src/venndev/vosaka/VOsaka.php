<?php

declare(strict_types=1);

namespace venndev\vosaka;

use Generator;
use InvalidArgumentException;
use RuntimeException;
use Throwable;
use venndev\vosaka\io\JoinHandle;
use venndev\vosaka\runtime\eventloop\EventLoop;
use venndev\vosaka\time\Sleep;
use venndev\vosaka\core\Result;

final class VOsaka
{
    private static EventLoop $eventLoop;
    private static int $taskCounter = 0;

    public static function getLoop(): EventLoop
    {
        if (!isset(self::$eventLoop)) {
            self::$eventLoop = new EventLoop();
        }

        return self::$eventLoop;
    }

    /**
     * Spawn a task and return a Result that can be used to await its completion
     */
    public static function spawn(callable|Generator $task, mixed $context = null): Result
    {
        return JoinHandle::c(self::getLoop()->spawn($task, $context));
    }

    /**
     * Spawn a task and return a Result that can be used to await its completion
     * This is the main entry point for creating asynchronous tasks
     */
    public static function join(callable|Generator|Result ...$tasks): Result
    {
        return self::spawn(self::processAllTasks(...$tasks));
    }

    /**
     * Try to join tasks - if any task fails, it will return the error
     * Returns null if all tasks complete successfully
     */
    public static function tryJoin(callable|Generator|Result ...$tasks): Result
    {
        $fn = function () use ($tasks): Generator {
            try {
                yield from self::processAllTasks(...$tasks);
            } catch (Throwable $e) {
                return $e;
            }
            return null;
        };

        return self::spawn($fn);
    }

    /**
     * Select the first task that completes, similar to Rust's select! macro
     * Returns a tuple [index, result] where index is the position of the completed task
     * and result is the value returned by that task.
     */
    public static function select(callable|Generator|Result ...$tasks): Result
    {
        return self::spawn(self::processSelectTasks(...$tasks));
    }

    /**
     * Select with timeout - returns the first task that completes within the timeout
     * Returns a tuple [index, result] or null if timeout is reached
     */
    public static function selectTimeout(float $timeoutSeconds, callable|Generator|Result ...$tasks): Result
    {
        $timeoutTask = function () use ($timeoutSeconds): Generator {
            yield Sleep::c($timeoutSeconds);
            return null; // Timeout indicator
        };

        // Add timeout task as the last task
        $allTasks = [...$tasks, $timeoutTask];

        return self::spawn(self::processSelectTasks(...$allTasks));
    }

    /**
     * Select with biased ordering - tasks are checked in order of priority
     * Earlier tasks have higher priority when multiple tasks are ready
     */
    public static function selectBiased(callable|Generator|Result ...$tasks): Result
    {
        return self::spawn(self::processSelectTasksBiased(...$tasks));
    }

    private static function processAllTasks(callable|Generator|Result ...$tasks): Generator
    {
        $spawnedTasks = [];
        foreach ($tasks as $task) {
            $spawnedTasks[] = $task instanceof Result
                ? $task
                : self::spawn($task);
            yield;
        }

        $results = [];
        foreach ($spawnedTasks as $spawned) {
            $results[] = yield from $spawned->unwrap();
        }

        return $results;
    }

    /**
     * Process tasks for select operation - returns first completed task with its index
     */
    private static function processSelectTasks(callable|Generator|Result ...$tasks): Generator
    {
        if (empty($tasks)) {
            throw new InvalidArgumentException('At least one task is required for select');
        }

        // Convert all tasks to generators and track their indices
        $generators = [];
        foreach ($tasks as $index => $task) {
            if ($task instanceof Result) {
                $generators[$index] = $task->unwrap();
            } else {
                $generators[$index] = self::spawn($task)->unwrap();
            }
        }

        // Poll all generators until one completes
        while (!empty($generators)) {
            foreach ($generators as $index => $generator) {
                // Check if generator is done
                if (!$generator->valid()) {
                    $result = $generator->getReturn();
                    return [$index, $result];
                }

                // Advance the generator
                $generator->next();

                // Check again after advancing
                if (!$generator->valid()) {
                    $result = $generator->getReturn();
                    return [$index, $result];
                }
            }

            // Yield control to allow other tasks to run
            yield;
        }

        throw new RuntimeException('All tasks completed unexpectedly');
    }

    /**
     * Process tasks with biased ordering (check tasks in order)
     */
    private static function processSelectTasksBiased(callable|Generator|Result ...$tasks): Generator
    {
        if (empty($tasks)) {
            throw new InvalidArgumentException('At least one task is required for select');
        }

        // Convert all tasks to generators and track their indices
        $generators = [];
        foreach ($tasks as $index => $task) {
            if ($task instanceof Result) {
                $generators[$index] = $task->unwrap();
            } else {
                $generators[$index] = self::spawn($task)->unwrap();
            }
        }

        // Poll generators in order (biased)
        while (!empty($generators)) {
            // Check each generator in order
            foreach ($generators as $index => $generator) {
                // Check if generator is done
                if (!$generator->valid()) {
                    $result = $generator->getReturn();
                    return [$index, $result];
                }
            }

            // Advance all generators
            foreach ($generators as $index => $generator) {
                if ($generator->valid()) {
                    $generator->next();

                    // Check if completed after advancing
                    if (!$generator->valid()) {
                        $result = $generator->getReturn();
                        return [$index, $result];
                    }
                }
            }

            // Yield control
            yield;
        }

        throw new RuntimeException('All tasks completed unexpectedly');
    }

    /**
     * Legacy method - kept for backward compatibility
     * @deprecated Use select() instead, which returns [index, result]
     */
    private static function processOneIndexedTasks(callable|Generator|Result ...$tasks): Generator
    {
        $spawnedTasks = [];
        foreach ($tasks as $task) {
            $spawnedTasks[] = $task instanceof Result
                ? $task->unwrap()
                : self::spawn($task)->unwrap();
        }

        $result = null;
        while (!empty($spawnedTasks)) {
            $spawned = array_shift($spawnedTasks);
            if ($spawned->valid()) {
                $spawned->next();
                $spawnedTasks[] = $spawned; // Re-add to the end of the queue
            } else {
                $result = $spawned->getReturn();
                break;
            }
            yield;
        }

        return $result;
    }

    public static function retry(
        callable $taskFactory,
        int $maxRetries = 3,
        int $delaySeconds = 1,
        int $backOffMultiplier = 2,
        ?callable $shouldRetry = null
    ): Result {
        $fn = function () use ($taskFactory, $maxRetries, $delaySeconds, $backOffMultiplier, $shouldRetry): Generator {
            $retries = 0;
            while ($retries < $maxRetries) {
                try {
                    $task = $taskFactory();
                    if (!$task instanceof Generator) {
                        throw new InvalidArgumentException('Task must return a Generator');
                    }
                    return yield from $task;
                } catch (Throwable $e) {
                    if ($shouldRetry && !$shouldRetry($e)) {
                        throw $e;
                    }
                    $retries++;
                    if ($retries >= $maxRetries) {
                        throw new RuntimeException("Task failed after {$maxRetries} retries", 0, $e);
                    }
                    $delay = (int) ($delaySeconds * pow($backOffMultiplier, $retries - 1));
                    yield Sleep::c($delay);
                }
            }
        };

        return self::spawn($fn());
    }

    public static function run(): void
    {
        self::getLoop()->run();
    }

    public static function close(): void
    {
        self::getLoop()->close();
    }
}
