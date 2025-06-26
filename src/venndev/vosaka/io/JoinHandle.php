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
 * of spawned asynchronous tasks. It implements a registry pattern where
 * each task gets a unique ID and corresponding JoinHandle instance that
 * can be used to wait for completion and retrieve results.
 *
 * The class uses a static registry to track all active handles and provides
 * methods for checking completion status, waiting for results, and cleaning
 * up completed tasks. It's designed to work seamlessly with VOsaka's
 * event loop and Result system.
 */
final class JoinHandle
{
    public mixed $result = null;
    public bool $done = false;
    public bool $justSpawned = true;

    /** @var array<int, self> */
    private static array $instances = [];

    /**
     * Private constructor to prevent direct instantiation.
     *
     * JoinHandle instances should only be created through the static
     * factory method c() to ensure proper registration and ID management.
     * The constructor is private to enforce this pattern.
     *
     * @param int $id The unique task ID for this handle
     */
    private function __construct(public int $id)
    {
        // Private constructor to prevent direct instantiation
    }

    /**
     * Create a new JoinHandle for tracking task completion.
     *
     * Factory method that creates a new JoinHandle instance for the specified
     * task ID and registers it in the static instances registry. Returns a
     * Result that can be awaited to get the task's final result.
     *
     * The 'c' stands for 'create' and follows the naming convention used
     * throughout VOsaka for factory methods.
     *
     * @param int $id The unique task ID to track
     * @return Result A Result that will resolve to the task's final result
     * @throws RuntimeException If a handle with the same ID already exists
     */
    public static function c(int $id): Result
    {
        if (isset(self::$instances[$id])) {
            throw new RuntimeException(
                "JoinHandle with ID {$id} already exists."
            );
        }

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
     * If the task just spawned and produced an error, the error is thrown
     * immediately. Otherwise, the result is stored for later retrieval.
     * Completed handles are cleaned up from the registry.
     *
     * @param int $id The task ID to mark as completed
     * @param mixed $result The result or error from the task
     * @return void
     * @throws Throwable|Error If the task failed and was just spawned
     */
    public static function done(int $id, mixed $result): void
    {
        $handle = self::getInstance($id);
        $handle->result = $result;
        $handle->done = true;

        if ($handle->justSpawned) {
            if ($result instanceof Throwable || $result instanceof Error) {
                unset(self::$instances[$id]); // clean up first
                throw $result;
            }

            unset(self::$instances[$id]); // cleanup
        }
    }

    /**
     * Check if a task with the given ID has completed.
     *
     * Returns true if the task has finished execution (either successfully
     * or with an error), false if it's still running or doesn't exist.
     * This is a non-blocking check that can be used for polling.
     *
     * @param int $id The task ID to check
     * @return bool True if the task is completed, false otherwise
     */
    public static function isDone(int $id): bool
    {
        $handle = self::$instances[$id] ?? null;
        return $handle?->done ?? false;
    }

    /**
     * Get a JoinHandle instance by ID from the registry.
     *
     * Internal method for retrieving JoinHandle instances from the static
     * registry. Throws an exception if no handle exists for the given ID.
     *
     * @param int $id The task ID to retrieve
     * @return self The JoinHandle instance for the given ID
     * @throws RuntimeException If no handle exists for the given ID
     */
    private static function getInstance(int $id): self
    {
        return self::$instances[$id] ??
            throw new RuntimeException(
                "JoinHandle with ID {$id} does not exist."
            );
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
     * @param self $handle The JoinHandle to wait for
     * @return Generator A generator that yields the task's final result
     */
    private static function tryingDone(self $handle): Generator
    {
        $handle->justSpawned = false;

        while (!$handle->done) {
            yield;
        }

        $result = $handle->result;
        unset(self::$instances[$handle->id]); // cleanup after finished

        return $result;
    }
}
