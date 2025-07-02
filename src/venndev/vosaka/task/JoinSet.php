<?php

declare(strict_types=1);

namespace venndev\vosaka\task;

use Generator;
use RuntimeException;
use Throwable;
use venndev\vosaka\core\Future;
use venndev\vosaka\core\interfaces\Option;
use venndev\vosaka\core\Result;
use venndev\vosaka\VOsaka;

/**
 * JoinSet - A collection of spawned tasks that can be awaited together.
 *
 * It supports:
 * - Spawning tasks and adding them to the set
 * - Waiting for the next task to complete
 * - Waiting for all tasks to complete
 * - Aborting all tasks
 * - Checking if the set is empty
 * - Detaching tasks from the set
 *
 * All operations use Generators for non-blocking execution and follow
 * the project's Result/Option patterns for error handling.
 */
final class JoinSet
{
    private array $tasks = [];
    private array $completedResults = [];
    private int $nextTaskId = 0;
    private bool $aborted = false;

    /**
     * Create a new empty JoinSet
     */
    public function __construct()
    {
        // Constructor intentionally left empty
    }

    /**
     * Create a new JoinSet (factory method)
     *
     * @return self
     */
    public static function new(): self
    {
        return new self();
    }

    /**
     * Spawn a task and add it to the JoinSet
     *
     * @param callable|Generator $task The task to spawn
     * @param mixed $context Optional context data
     * @return Result<int> Returns the task ID
     */
    public function spawn(
        callable|Generator $task,
        mixed $context = null
    ): Result {
        $fn = function () use ($task, $context): Generator {
            if ($this->aborted) {
                throw new RuntimeException("JoinSet has been aborted");
            }

            $taskId = $this->nextTaskId++;
            $result = VOsaka::spawn($task, $context);
            $joinSetTask = new JoinSetTask($taskId, $result, $context);
            $this->tasks[$taskId] = $joinSetTask;

            VOsaka::spawn($this->monitorTask($joinSetTask));

            yield $taskId;
            return $taskId;
        };

        return Future::new($fn());
    }

    /**
     * Spawn a task with a specific key/identifier
     *
     * @param mixed $key The key to associate with the task
     * @param callable|Generator $task The task to spawn
     * @param mixed $context Optional context data
     * @return Result<int> Returns the task ID
     */
    public function spawnWithKey(
        mixed $key,
        callable|Generator $task,
        mixed $context = null
    ): Result {
        $fn = function () use ($key, $task, $context): Generator {
            $taskId = yield from $this->spawn($task, $context)->unwrap();
            if (isset($this->tasks[$taskId])) {
                $this->tasks[$taskId]->setKey($key);
            }
            return $taskId;
        };

        return Future::new($fn());
    }

    /**
     * Wait for the next task to complete and return its result
     *
     * @return Result<Option> Returns Some([taskId, result]) or None if empty
     */
    public function joinNext(): Result
    {
        $fn = function (): Generator {
            if ($this->isEmpty()) {
                return Future::none();
            }

            if (!empty($this->completedResults)) {
                $taskId = array_key_first($this->completedResults);
                $result = $this->completedResults[$taskId];
                unset($this->completedResults[$taskId]);
                unset($this->tasks[$taskId]);
                return Future::some([$taskId, $result]);
            }

            while (!empty($this->tasks) && empty($this->completedResults)) {
                yield;
            }

            if (!empty($this->completedResults)) {
                $taskId = array_key_first($this->completedResults);
                $result = $this->completedResults[$taskId];
                unset($this->completedResults[$taskId]);
                unset($this->tasks[$taskId]);
                return Future::some([$taskId, $result]);
            }

            return Future::none();
        };

        return Future::new($fn());
    }

    /**
     * Wait for the next task to complete with a key and return its result
     *
     * @return Result<Option> Returns Some([key, taskId, result]) or None if empty
     */
    public function joinNextWithKey(): Result
    {
        $fn = function (): Generator {
            $nextResult = yield from $this->joinNext()->unwrap();

            if ($nextResult->isNone()) {
                return Future::none();
            }

            [$taskId, $result] = $nextResult->unwrap();

            $key = null;
            foreach ($this->tasks as $task) {
                if ($task->getId() === $taskId) {
                    $key = $task->getKey();
                    break;
                }
            }

            return Future::some([$key, $taskId, $result]);
        };

        return Future::new($fn());
    }

    /**
     * Wait for all tasks to complete and return their results
     *
     * @return Result<array> Returns array of [taskId => result]
     */
    public function joinAll(): Result
    {
        $fn = function (): Generator {
            $results = [];

            while (!$this->isEmpty()) {
                $nextResult = yield from $this->joinNext()->unwrap();
                if ($nextResult->isSome()) {
                    [$taskId, $result] = $nextResult->unwrap();
                    $results[$taskId] = $result;
                }
            }

            return $results;
        };

        return Future::new($fn());
    }

    /**
     * Try to join the next task without waiting
     *
     * @return Option Returns Some([taskId, result]) or None if no task is ready
     */
    public function tryJoinNext(): Option
    {
        if (!empty($this->completedResults)) {
            $taskId = array_key_first($this->completedResults);
            $result = $this->completedResults[$taskId];
            unset($this->completedResults[$taskId]);
            unset($this->tasks[$taskId]);
            return Future::some([$taskId, $result]);
        }

        return Future::none();
    }

    /**
     * Abort all tasks in the JoinSet
     *
     * @return Result<int> Returns the number of tasks that were aborted
     */
    public function abortAll(): Result
    {
        $fn = function (): Generator {
            $abortedCount = 0;
            $this->aborted = true;

            foreach ($this->tasks as $task) {
                $task->abort();
                $abortedCount++;
                yield;
            }

            $this->tasks = [];
            $this->completedResults = [];

            return $abortedCount;
        };

        return Future::new($fn());
    }

    /**
     * Abort a specific task by ID
     *
     * @param int $taskId The task ID to abort
     * @return Result<bool> Returns true if task was found and aborted
     */
    public function abort(int $taskId): Result
    {
        $fn = function () use ($taskId): Generator {
            yield;
            if (isset($this->tasks[$taskId])) {
                $this->tasks[$taskId]->abort();
                unset($this->tasks[$taskId]);
                unset($this->completedResults[$taskId]);
                return true;
            }

            return false;
        };

        return Future::new($fn());
    }

    /**
     * Detach a task from the JoinSet (let it run but don't track it)
     *
     * @param int $taskId The task ID to detach
     * @return bool Returns true if task was found and detached
     */
    public function detach(int $taskId): bool
    {
        if (isset($this->tasks[$taskId])) {
            $this->tasks[$taskId]->detach();
            unset($this->tasks[$taskId]);
            unset($this->completedResults[$taskId]);
            return true;
        }

        return false;
    }

    /**
     * Check if the JoinSet is empty
     *
     * @return bool True if no tasks are being tracked
     */
    public function isEmpty(): bool
    {
        return empty($this->tasks) && empty($this->completedResults);
    }

    /**
     * Get the number of tasks currently in the JoinSet
     *
     * @return int The number of active tasks
     */
    public function len(): int
    {
        return count($this->tasks);
    }

    /**
     * Clear all tasks from the JoinSet without aborting them
     *
     * @return int The number of tasks that were detached
     */
    public function clear(): int
    {
        $count = count($this->tasks);

        foreach ($this->tasks as $task) {
            $task->detach();
        }

        $this->tasks = [];
        $this->completedResults = [];

        return $count;
    }

    /**
     * Get all task IDs currently in the JoinSet
     *
     * @return array<int> Array of task IDs
     */
    public function taskIds(): array
    {
        return array_keys($this->tasks);
    }

    /**
     * Check if a specific task ID exists in the JoinSet
     *
     * @param int $taskId The task ID to check
     * @return bool True if the task exists
     */
    public function contains(int $taskId): bool
    {
        return isset($this->tasks[$taskId]);
    }

    /**
     * Monitor a task and handle its completion
     *
     * @param JoinSetTask $joinSetTask The task to monitor
     * @return Generator
     */
    private function monitorTask(JoinSetTask $joinSetTask): Generator
    {
        try {
            $result = yield from $joinSetTask->getResult()->unwrap();

            if (!$joinSetTask->isAborted() && !$joinSetTask->isDetached()) {
                $this->completedResults[$joinSetTask->getId()] = $result;
            }
        } catch (Throwable $e) {
            if (!$joinSetTask->isAborted() && !$joinSetTask->isDetached()) {
                $this->completedResults[$joinSetTask->getId()] = $e;
            }
        }
    }
}
