<?php

declare(strict_types=1);

namespace venndev\vosaka\io;

use Error;
use Generator;
use RuntimeException;
use Throwable;
use venndev\vosaka\core\Result;

/**
 * JoinHandle class for tracking and waiting on asynchronous task completion.
 *
 * This class provides a handle for tracking the execution state and result
 * of spawned asynchronous tasks. It implements a registry pattern using an
 * indexed array where each task gets a unique ID and corresponding JoinHandle
 * instance that can be used to wait for completion and retrieve results.
 */
final class JoinHandle
{
    public mixed $result = null;
    public mixed $yieldData = null;
    public bool $done = false;
    public bool $justSpawned = true;

    /**
     * Registry of active JoinHandle instances indexed by task ID.
     *
     * @var array<int, self>
     */
    private static array $instances = [];

    /**
     * Private constructor to prevent direct instantiation.
     *
     * JoinHandle instances should only be created through the static
     * factory method new() to ensure proper registration and ID management.
     *
     * @param int $id The unique task ID for this handle
     */
    public function __construct(public int $id) {}

    /**
     * Attempt to yield data for a task with the given ID.
     *
     * This method allows a task to yield data back to the event loop, which
     * can be used by other coroutines waiting on this task. The data is stored
     * in the JoinHandle instance associated with the task ID.
     *
     * @param int $id The unique task ID to yield data for
     * @param mixed $data The data to yield back to the event loop
     * @throws RuntimeException If no handle exists for the given ID
     */
    public static function tryYield(int $id, mixed $data): void
    {
        if (!isset(self::$instances[$id])) {
            throw new RuntimeException("JoinHandle with ID {$id} does not exist.");
        }

        self::$instances[$id]->yieldData = $data;
    }

    /**
     * Create a new JoinHandle for tracking task completion.
     *
     * Factory method that creates a new JoinHandle instance for the specified
     * task ID and registers it in the static array registry. Returns a
     * Result that can be awaited to get the task's final result.
     *
     * If a handle with the same ID already exists and is still active,
     * it will be replaced. This allows for natural reuse of IDs across
     * different execution contexts (benchmarks, tests, etc.).
     *
     * @param int $id The unique task ID to track
     * @return Result A Result that will resolve to the task's final result
     */
    public static function new(int $id): Result
    {
        $handle = new self($id);
        self::$instances[$id] = $handle;

        return new Result(self::tryingDone($handle));
    }

    /**
     * Mark a task as completed with the given result.
     *
     * Called by the event loop when a task completes (successfully or with
     * an error). Sets the result and marks the handle as done, which will
     * cause any waiting coroutines to receive the result.
     *
     * The result is always stored in the handle for retrieval by waiting
     * coroutines. Errors are not thrown immediately - they are stored and
     * will be handled by the waiting coroutine in tryingDone().
     *
     * Completed handles are NOT cleaned up here - they are cleaned up
     * when the waiting coroutine retrieves the result in tryingDone().
     *
     * @param int $id The task ID to mark as completed
     * @param mixed $result The result or error from the task
     * @return void
     * @throws RuntimeException If no handle exists for the given ID
     */
    public static function done(int $id, mixed $result): void
    {
        if (!isset(self::$instances[$id])) {
            // Silently ignore if handle doesn't exist - task might have been cleaned up
            return;
        }

        $handle = self::$instances[$id];
        $handle->result = $result;
        $handle->done = true;

        // Do NOT throw exceptions here - let the waiting coroutine handle them
        // Do NOT clean up here - cleanup happens in tryingDone()
    }

    /**
     * Check if a task with the given ID has completed.
     *
     * Returns true if the task has finished execution (either successfully
     * or with an error), false if it's still running or doesn't exist.
     *
     * @param int $id The task ID to check
     * @return bool True if the task is completed, false otherwise
     */
    public static function isDone(int $id): bool
    {
        return isset(self::$instances[$id]) && self::$instances[$id]->done;
    }

    /**
     * Generator that waits for task completion and returns the result.
     *
     * Internal generator method that implements the waiting logic for task
     * completion. Marks the handle as no longer just spawned, then yields
     * control to the event loop until the task is marked as done.
     *
     * Once the task completes, retrieves the result, cleans up the handle
     * from the registry, and returns the final result.
     *
     * If the task just spawned and the result is an error, the error is
     * thrown here rather than in done() to avoid crashing the event loop.
     *
     * @param self $handle The JoinHandle to wait for
     * @return Generator A generator that yields the task's final result
     * @throws Throwable|Error If the task failed and was just spawned
     */
    private static function tryingDone(self $handle): Generator
    {
        $justSpawned = $handle->justSpawned;
        $handle->justSpawned = false;

        while (!$handle->done) {
            yield $handle->yieldData;
        }

        $result = $handle->result;
        unset(self::$instances[$handle->id]);

        // If the task just spawned and resulted in an error, throw it here
        if ($justSpawned && ($result instanceof Throwable || $result instanceof Error)) {
            throw $result;
        }

        return $result;
    }

    /**
     * Get the current number of active handles in the registry.
     *
     * Utility method for debugging and monitoring purposes.
     *
     * @return int The number of active JoinHandle instances
     */
    public static function getActiveCount(): int
    {
        return count(self::$instances);
    }

    /**
     * Get all active task IDs.
     *
     * Utility method for debugging and monitoring purposes.
     *
     * @return array<int> Array of active task IDs
     */
    public static function getActiveIds(): array
    {
        return array_keys(self::$instances);
    }

    /**
     * Clear all handles from the registry.
     *
     * Utility method for cleanup, primarily used in testing scenarios.
     * Use with caution in production as it may cause waiting tasks to hang.
     *
     * @return void
     */
    public static function clearAll(): void
    {
        self::$instances = [];
    }
}
