<?php

declare(strict_types=1);

namespace venndev\vosaka;

use Generator;
use venndev\vosaka\core\Defer;
use venndev\vosaka\core\Result;
use venndev\vosaka\eventloop\EventLoop;

/**
 * Initialize the VOsaka event loop and start processing tasks.
 *
 * This function starts the main event loop which will continue running until
 * all spawned tasks have completed or the loop is explicitly closed.
 * This is typically called once at the end of your program to start
 * the asynchronous runtime.
 *
 * @return void
 */
function run(): void
{
    VOsaka::run();
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
function close(): void
{
    VOsaka::close();
}

/**
 * Get the singleton EventLoop instance.
 *
 * Returns the global event loop instance, creating it if it doesn't exist.
 * This ensures all VOsaka operations share the same event loop for
 * coordinated task execution.
 *
 * @return EventLoop The global event loop instance
 */
function getLoop(): EventLoop
{
    return VOsaka::getLoop();
}

/**
 * Spawn an asynchronous task and return a Result for awaiting completion.
 *
 * Creates a new asynchronous task from the provided callable or generator
 * and schedules it for execution in the event loop. The task will run
 * concurrently with other tasks and can be awaited using the returned Result.
 *
 * @param callable|Generator $callback The task to spawn (callable or generator)
 * @param mixed $context Optional context data passed to the task
 * @return Result A Result object that can be used to await the task's completion
 */
function spawn(callable|Generator $callback, mixed $context = null): Result
{
    return VOsaka::spawn($callback, $context);
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
function join(callable|Generator|Result ...$tasks): Result
{
    return VOsaka::join(...$tasks);
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
function tryJoin(callable|Generator|Result ...$tasks): Result
{
    return VOsaka::tryJoin(...$tasks);
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
function select(callable|Generator|Result ...$tasks): Result
{
    return VOsaka::select(...$tasks);
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
function selectTimeout(
    float $timeoutSeconds,
    callable|Generator|Result ...$tasks
): Result {
    return VOsaka::selectTimeout($timeoutSeconds, ...$tasks);
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
function selectBiased(callable|Generator|Result ...$tasks): Result
{
    return VOsaka::selectBiased(...$tasks);
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
function retry(
    callable $taskFactory,
    int $maxRetries = 3,
    int $delaySeconds = 1,
    int $backOffMultiplier = 2,
    ?callable $shouldRetry = null
): Result {
    return VOsaka::retry($taskFactory, $maxRetries, $delaySeconds, $backOffMultiplier, $shouldRetry);
}

/**
 * Create a new Defer instance for deferred execution.
 *
 * This function creates a new Defer object that allows you to schedule
 * a callback to be executed later, typically at the end of the current
 * event loop iteration. This is useful for cleanup tasks or finalization
 * logic that should run after all other tasks have completed.
 *
 * @param callable $callback The callback to execute when deferred
 * @return Defer A new Defer instance
 */
function defer(callable $callback): Defer
{
    return Defer::new($callback);
}
