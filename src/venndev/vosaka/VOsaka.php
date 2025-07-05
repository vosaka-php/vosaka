<?php

declare(strict_types=1);

namespace venndev\vosaka;

use Generator;
use InvalidArgumentException;
use RuntimeException;
use Throwable;
use venndev\vosaka\core\Future;
use venndev\vosaka\io\JoinHandle;
use venndev\vosaka\runtime\eventloop\EventLoop;
use venndev\vosaka\time\Sleep;
use venndev\vosaka\core\Result;

/**
 * VOsaka - Main entry point for the asynchronous runtime system.
 *
 * This class provides the primary API for creating, managing, and executing
 * asynchronous tasks using generators. It serves as a facade over the event
 * loop and provides high-level operations for:
 *
 * - Spawning individual tasks with spawn()
 * - Joining multiple tasks with join()
 * - Trying to join tasks with error handling via tryJoin()
 * - Selecting the first completed task with select()
 * - Retrying failed tasks with configurable backoff
 * - Running the event loop and managing its lifecycle
 * - And more...
 *
 * All task operations return Result objects that can be awaited using
 * generator-based coroutines, enabling non-blocking asynchronous execution.
 */
final class VOsaka
{
    private static EventLoop $eventLoop;
    private static int $taskCounter = 0;

    /**
     * Get the singleton EventLoop instance.
     *
     * Returns the global event loop instance, creating it if it doesn't exist.
     * This ensures all VOsaka operations share the same event loop for
     * coordinated task execution.
     *
     * @return EventLoop The global event loop instance
     */
    public static function getLoop(): EventLoop
    {
        if (!isset(self::$eventLoop)) {
            self::$eventLoop = new EventLoop();
        }
        return self::$eventLoop;
    }

    /**
     * Spawn an asynchronous task and return a Result for awaiting completion.
     *
     * Creates a new asynchronous task from the provided callable or generator
     * and schedules it for execution in the event loop. The task will run
     * concurrently with other tasks and can be awaited using the returned Result.
     *
     * @param callable|Generator $task The task to spawn (callable or generator)
     * @param mixed $context Optional context data passed to the task
     * @return Result A Result object that can be used to await the task's completion
     */
    public static function spawn(
        callable|Generator $task,
        mixed $context = null
    ): Result {
        return JoinHandle::new(self::getLoop()->spawn($task, $context));
    }

    /**
     * Join multiple tasks and wait for all of them to complete.
     *
     * Executes multiple tasks concurrently and waits for all of them to finish.
     * Returns an array containing the results of all tasks in the same order
     * they were provided. If any task fails, the entire join operation fails.
     *
     * This is the main entry point for concurrent task execution when you need
     * all tasks to complete successfully.
     *
     * @param callable|Generator|Result ...$tasks The tasks to join and wait for
     * @return Result A Result containing an array of all task results
     */
    public static function join(callable|Generator|Result ...$tasks): Result
    {
        return Future::new(self::processAllTasks(...$tasks));
    }

    /**
     * Try to join multiple tasks with error handling.
     *
     * Similar to join() but provides graceful error handling. If any task fails,
     * returns the error instead of throwing an exception. Returns null if all
     * tasks complete successfully, making it easy to check for success.
     *
     * This is useful when you want to handle task failures explicitly rather
     * than having them propagate as exceptions.
     *
     * @param callable|Generator|Result ...$tasks The tasks to try joining
     * @return Result A Result containing null on success or the error on failure
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

        return Future::new($fn());
    }

    /**
     * Select the first task that completes.
     *
     * Executes multiple tasks concurrently and returns as soon as the first
     * one completes. The result is a tuple [index, result] where index is
     * the position of the completed task and result is the value it returned.
     *
     * This is useful for implementing timeouts, racing multiple operations,
     * or handling whichever operation completes first.
     *
     * @param callable|Generator|Result ...$tasks The tasks to race
     * @return Result A Result containing [index, result] of the first completed task
     */
    public static function select(callable|Generator|Result ...$tasks): Result
    {
        return Future::new(self::processSelectTasks(...$tasks));
    }

    /**
     * Select the first task that completes within a timeout period.
     *
     * Similar to select() but with a timeout mechanism. Returns the first task
     * that completes within the specified timeout, or null if the timeout is
     * reached before any task completes. The timeout task is automatically
     * added to the selection.
     *
     * @param float $timeoutSeconds Maximum time to wait in seconds
     * @param callable|Generator|Result ...$tasks The tasks to race with timeout
     * @return Result A Result containing [index, result] or null if timeout
     */
    public static function selectTimeout(
        float $timeoutSeconds,
        callable|Generator|Result ...$tasks
    ): Result {
        $timeoutTask = function () use ($timeoutSeconds): Generator {
            yield Sleep::new($timeoutSeconds);
            return null;
        };

        $allTasks = [...$tasks, $timeoutTask];
        return Future::new(self::processSelectTasks(...$allTasks));
    }

    /**
     * Select with biased ordering - tasks are checked in priority order.
     *
     * Similar to select() but with deterministic ordering. Tasks are checked
     * in the order they were provided, giving earlier tasks higher priority
     * when multiple tasks are ready simultaneously. This ensures predictable
     * behavior in scenarios where task completion order matters.
     *
     * @param callable|Generator|Result ...$tasks The tasks to select from (in priority order)
     * @return Result A Result containing [index, result] of the first completed task
     */
    public static function selectBiased(
        callable|Generator|Result ...$tasks
    ): Result {
        return Future::new(self::processSelectTasksBiased(...$tasks));
    }

    private static function processAllTasks(
        callable|Generator|Result ...$tasks
    ): Generator {
        $taskCount = count($tasks);
        if ($taskCount === 0) {
            return [];
        }

        $generators = [];
        $results = [];

        for ($i = 0; $i < $taskCount; $i++) {
            $task = $tasks[$i];
            $generators[] = ($task instanceof Result
                ? $task
                : self::spawn($task)
            )->unwrap();
            $results[] = null;
        }

        yield;

        $completedMask = 0;
        $allCompletedMask = (1 << $taskCount) - 1;

        while ($completedMask !== $allCompletedMask) {
            for ($i = 0; $i < $taskCount; $i++) {
                if ($completedMask & (1 << $i)) {
                    continue;
                }

                $gen = $generators[$i];
                if ($gen->valid()) {
                    $gen->next();
                }

                if (!$gen->valid()) {
                    $results[$i] = $gen->getReturn();
                    $completedMask |= 1 << $i;
                }
            }

            if ($completedMask !== $allCompletedMask) {
                yield;
            }
        }

        return $results;
    }

    /**
     * Process tasks for select operation - returns first completed task with its index.
     *
     * Internal method that handles the concurrent execution and monitoring of
     * tasks for the select() operation. Continuously polls all tasks until
     * one completes, then returns its index and result.
     *
     * @param callable|Generator|Result ...$tasks The tasks to process
     * @return Generator A generator that yields [index, result] of first completed task
     * @throws InvalidArgumentException If no tasks are provided
     * @throws RuntimeException If all tasks complete unexpectedly
     */
    private static function processSelectTasks(
        callable|Generator|Result ...$tasks
    ): Generator {
        if (empty($tasks)) {
            throw new InvalidArgumentException(
                "At least one task is required for select"
            );
        }

        $generators = [];
        foreach ($tasks as $index => $task) {
            $generators[$index] =
                $task instanceof Result
                    ? $task->unwrap()
                    : self::spawn($task)->unwrap();
        }

        while (!empty($generators)) {
            foreach ($generators as $index => $generator) {
                if (!$generator->valid()) {
                    $result = $generator->getReturn();
                    return [$index, $result];
                }

                $generator->next();

                if (!$generator->valid()) {
                    $result = $generator->getReturn();
                    return [$index, $result];
                }
            }

            yield;
        }

        throw new RuntimeException("All tasks completed unexpectedly");
    }

    /**
     * Process tasks with biased ordering (check tasks in priority order).
     *
     * Internal method for selectBiased() that implements deterministic task
     * checking. Tasks are always checked in the order provided, ensuring
     * earlier tasks have priority when multiple are ready.
     *
     * @param callable|Generator|Result ...$tasks The tasks to process in order
     * @return Generator A generator that yields [index, result] of first completed task
     * @throws InvalidArgumentException If no tasks are provided
     * @throws RuntimeException If all tasks complete unexpectedly
     */
    private static function processSelectTasksBiased(
        callable|Generator|Result ...$tasks
    ): Generator {
        if (empty($tasks)) {
            throw new InvalidArgumentException(
                "At least one task is required for select"
            );
        }

        $generators = [];
        foreach ($tasks as $index => $task) {
            $generators[$index] =
                $task instanceof Result
                    ? $task->unwrap()
                    : self::spawn($task)->unwrap();
        }

        while (!empty($generators)) {
            foreach ($generators as $index => $generator) {
                if (!$generator->valid()) {
                    $result = $generator->getReturn();
                    return [$index, $result];
                }
            }

            foreach ($generators as $index => $generator) {
                if ($generator->valid()) {
                    $generator->next();

                    if (!$generator->valid()) {
                        $result = $generator->getReturn();
                        return [$index, $result];
                    }
                }
            }

            yield;
        }

        throw new RuntimeException("All tasks completed unexpectedly");
    }

    /**
     * Legacy method - kept for backward compatibility
     * @deprecated Use select() instead, which returns [index, result]
     */
    private static function processOneIndexedTasks(
        callable|Generator|Result ...$tasks
    ): Generator {
        $spawnedTasks = [];
        foreach ($tasks as $task) {
            $spawnedTasks[] =
                $task instanceof Result
                    ? $task->unwrap()
                    : self::spawn($task)->unwrap();
        }

        $result = null;
        while (!empty($spawnedTasks)) {
            $spawned = array_shift($spawnedTasks);
            if ($spawned->valid()) {
                $spawned->next();
                $spawnedTasks[] = $spawned;
            } else {
                $result = $spawned->getReturn();
                break;
            }
            yield;
        }

        return $result;
    }

    /**
     * Retry a task with configurable retry logic and exponential backoff.
     *
     * Executes a task factory function with automatic retry on failure.
     * Supports configurable retry count, delay between attempts, exponential
     * backoff multiplier, and custom retry condition logic.
     *
     * The task factory should return a Generator that represents the task
     * to be executed. If the task fails, it will be retried according to
     * the specified parameters.
     *
     * @param callable $taskFactory Factory function that returns a Generator task
     * @param int $maxRetries Maximum number of retry attempts (default: 3)
     * @param int $delaySeconds Initial delay between retries in seconds (default: 1)
     * @param int $backOffMultiplier Multiplier for exponential backoff (default: 2)
     * @param callable|null $shouldRetry Optional predicate to determine if retry should occur
     * @return Result A Result containing the task result or final failure
     * @throws InvalidArgumentException If task factory doesn't return a Generator
     * @throws RuntimeException If all retries are exhausted
     */
    public static function retry(
        callable $taskFactory,
        int $maxRetries = 3,
        int $delaySeconds = 1,
        int $backOffMultiplier = 2,
        ?callable $shouldRetry = null
    ): Result {
        $fn = function () use (
            $taskFactory,
            $maxRetries,
            $delaySeconds,
            $backOffMultiplier,
            $shouldRetry
        ): Generator {
            $retries = 0;
            while ($retries < $maxRetries) {
                try {
                    $task = $taskFactory();
                    if (!$task instanceof Generator) {
                        throw new InvalidArgumentException(
                            "Task must return a Generator"
                        );
                    }
                    return yield from $task;
                } catch (Throwable $e) {
                    if ($shouldRetry && !$shouldRetry($e)) {
                        throw $e;
                    }
                    $retries++;
                    if ($retries >= $maxRetries) {
                        throw new RuntimeException(
                            "Task failed after {$maxRetries} retries",
                            0,
                            $e
                        );
                    }
                    $delay =
                        (int) ($delaySeconds *
                            pow($backOffMultiplier, $retries - 1));
                    yield Sleep::new($delay);
                }
            }
        };

        return Future::new($fn());
    }

    /**
     * Start the event loop and run until all tasks complete.
     *
     * Begins execution of the event loop, which will continue running until
     * all spawned tasks have completed or the loop is explicitly closed.
     * This is typically called once at the end of your program to start
     * the asynchronous runtime.
     *
     * @return void
     */
    public static function run(): void
    {
        self::getLoop()->run();
    }

    /**
     * Close the event loop and stop all task processing.
     *
     * Gracefully shuts down the event loop, stopping all task processing
     * and cleaning up resources. This will cause the run() method to exit
     * and should be called when you want to terminate the asynchronous runtime.
     *
     * @return void
     */
    public static function close(): void
    {
        self::getLoop()->close();
    }
}
