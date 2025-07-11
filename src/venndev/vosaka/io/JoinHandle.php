<?php

declare(strict_types=1);

namespace venndev\vosaka\io;

use Error;
use Generator;
use RuntimeException;
use Throwable;
use WeakMap;
use venndev\vosaka\core\Result;

/**
 * JoinHandle class for tracking and waiting on asynchronous task completion.
 *
 * This class provides a handle for tracking the execution state and result
 * of spawned asynchronous tasks. It implements a registry pattern using WeakMap
 * where each task gets a unique ID and corresponding JoinHandle instance that
 * can be used to wait for completion and retrieve results.
 */
final class JoinHandle
{
    public mixed $result = null;
    public mixed $yieldData = null;
    public bool $done = false;
    public bool $justSpawned = true;
    private static WeakMap $instances;

    /**
     * Private constructor to prevent direct instantiation.
     *
     * JoinHandle instances should only be created through the static
     * factory method c() to ensure proper registration and ID management.
     *
     * @param int $id The unique task ID for this handle
     */
    public function __construct(public int $id)
    {
        self::$instances ??= new WeakMap();
    }

    /**
     * Attempt to yield data for a task with the given ID.
     *
     * This method allows a task to yield data back to the event loop, which
     * can be used by other coroutines waiting on this task. The data is stored
     * in the JoinHandle instance associated with the task ID.
     *
     * @param int $id The unique task ID to yield data for
     * @param mixed $data The data to yield back to the event loop
     */
    public static function tryYield(int $id, mixed $data): void
    {
        $handle = self::getInstance($id);
        $handle->yieldData = $data;
    }

    /**
     * Create a new JoinHandle for tracking task completion.
     *
     * Factory method that creates a new JoinHandle instance for the specified
     * task ID and registers it in the static WeakMap registry. Returns a
     * Result that can be awaited to get the task's final result.
     *
     * @param int $id The unique task ID to track
     * @return Result A Result that will resolve to the task's final result
     * @throws RuntimeException If a handle with the same ID already exists
     */
    public static function new(int $id): Result
    {
        $handle = new self($id);
        self::$instances[$handle] = $handle;
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
     * Completed handles are cleaned up from the WeakMap registry.
     *
     * @param int $id The task ID to mark as completed
     * @param mixed $result The result or error from the task
     * @return void
     * @throws Throwable|Error If the task failed and was just spawned
     */
    public static function done(int $id, mixed $result): void
    {
        foreach (self::$instances as $handle) {
            if ($handle->id === $id) {
                $handle->result = $result;
                $handle->done = true;

                if (
                    $handle->justSpawned &&
                    ($result instanceof Throwable || $result instanceof Error)
                ) {
                    unset(self::$instances[$handle]);
                    throw $result;
                }

                unset(self::$instances[$handle]);
                break;
            }
        }
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
        foreach (self::$instances as $handle) {
            if ($handle->id === $id) {
                return $handle->done;
            }
        }
        return false;
    }

    /**
     * Get a JoinHandle instance by ID from the WeakMap registry.
     *
     * Internal method for retrieving JoinHandle instances from the static
     * WeakMap registry. Throws an exception if no handle exists for the given ID.
     *
     * @param int $id The task ID to retrieve
     * @return self The JoinHandle instance for the given ID
     * @throws RuntimeException If no handle exists for the given ID
     */
    private static function getInstance(int $id): self
    {
        foreach (self::$instances as $handle) {
            if ($handle->id === $id) {
                return $handle;
            }
        }
        throw new RuntimeException("JoinHandle with ID {$id} does not exist.");
    }

    /**
     * Generator that waits for task completion and returns the result.
     *
     * Internal generator method that implements the waiting logic for task
     * completion. Marks the handle as no longer just spawned, then yields
     * control to the event loop until the task is marked as done.
     *
     * Once the task completes, retrieves the result, cleans up the handle
     * from the WeakMap registry, and returns the final result.
     *
     * @param self $handle The JoinHandle to wait for
     * @return Generator A generator that yields the task's final result
     */
    private static function tryingDone(self $handle): Generator
    {
        $handle->justSpawned = false;

        while (! $handle->done) {
            yield $handle->yieldData;
        }

        $result = $handle->result;
        unset(self::$instances[$handle]);

        return $result;
    }
}
