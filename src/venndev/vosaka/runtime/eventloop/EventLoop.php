<?php

declare(strict_types=1);

namespace venndev\vosaka\runtime\eventloop;

use Generator;
use InvalidArgumentException;
use RuntimeException;
use SplPriorityQueue;
use Throwable;
use venndev\vosaka\cleanup\GracefulShutdown;
use venndev\vosaka\io\JoinHandle;
use venndev\vosaka\runtime\eventloop\task\TaskPool;
use venndev\vosaka\runtime\eventloop\task\TaskState;
use venndev\vosaka\runtime\eventloop\task\Task;
use venndev\vosaka\core\MemoryManager;
use venndev\vosaka\time\Interval;
use venndev\vosaka\time\Sleep;
use venndev\vosaka\utils\CallableUtil;
use venndev\vosaka\utils\GeneratorUtil;
use venndev\vosaka\utils\MemUtil;
use venndev\vosaka\utils\Defer;
use venndev\vosaka\utils\sync\CancelFuture;

/**
 * EventLoop class manages the asynchronous task execution runtime.
 *
 * This is the core of the VOsaka asynchronous runtime, responsible for:
 * - Spawning and managing asynchronous tasks
 * - Handling task scheduling and execution
 * - Managing memory usage and garbage collection
 * - Providing backpressure control and queue size limits
 * - Graceful shutdown and cleanup operations
 *
 * The EventLoop uses a priority queue to manage ready tasks and maintains
 * separate collections for running and deferred tasks. It implements
 * various performance optimizations including cycle limits, execution
 * time limits, and memory management.
 */
final class EventLoop
{
    private SplPriorityQueue $readyQueue;
    private TaskPool $taskPool;
    private ?MemoryManager $memoryManager = null;
    private ?GracefulShutdown $gracefulShutdown = null;
    private array $runningTasks = [];
    private array $deferredTasks = [];
    private bool $isRunning = false;

    // Memory monitoring
    private int $maxMemoryUsage;
    private int $taskProcessedCount = 0;
    private float $startTime;

    // Optimized limits
    private int $maxTasksPerCycle = 15;
    private int $maxQueueSize = 4000;
    private float $maxExecutionTime = 0.08;
    private int $currentCycleTaskCount = 0;
    private float $cycleStartTime = 0.0;

    // Backpressure handling
    private bool $enableBackpressure = true;
    private int $backpressureThreshold = 3200; // 80% of max queue
    private int $droppedTasks = 0;

    // Control the number of iterations
    private int $iterationLimit = 1;
    private int $currentIteration = 0;
    private bool $enableIterationLimit = false;

    // Cache queue size to avoid repeated calls
    private int $queueSize = 0;

    /**
     * Constructor for EventLoop.
     *
     * Initializes the event loop with configurable memory limits and sets up
     * the task management infrastructure including the ready queue and task pool.
     *
     * @param int $maxMemoryMB Maximum memory usage in megabytes (default: 128MB)
     */
    public function __construct(int $maxMemoryMB = 128)
    {
        // Reduced default memory limit
        $this->readyQueue = new SplPriorityQueue();
        $this->taskPool = new TaskPool();
        $this->maxMemoryUsage = MemUtil::toKB($maxMemoryMB);
    }

    /**
     * Get or create the memory manager instance.
     *
     * Returns a singleton MemoryManager instance that monitors and controls
     * memory usage within the specified limits. Creates the instance on first
     * access using lazy initialization.
     *
     * @return MemoryManager The memory manager instance
     */
    public function getMemoryManager(): MemoryManager
    {
        return $this->memoryManager ??= new MemoryManager(
            $this->maxMemoryUsage
        );
    }

    /**
     * Get or create the graceful shutdown manager instance.
     *
     * Returns a singleton GracefulShutdown instance that handles cleanup
     * operations and temporary file management during shutdown. Creates
     * the instance on first access using lazy initialization.
     *
     * @return GracefulShutdown The graceful shutdown manager instance
     */
    public function getGracefulShutdown(): GracefulShutdown
    {
        return $this->gracefulShutdown ??= new GracefulShutdown();
    }

    /**
     * Spawn a new asynchronous task in the event loop.
     *
     * Creates a new task from the provided callable or generator and adds it to
     * the ready queue for execution. The task will be executed asynchronously
     * as part of the event loop's task scheduling.
     *
     * @param callable|Generator $task The task to spawn (callable or generator)
     * @param mixed $context Optional context data to pass to the task
     * @return int The unique task ID for tracking the spawned task
     * @throws RuntimeException If the task queue is full and backpressure is enabled
     */
    public function spawn(callable|Generator $task, mixed $context = null): int
    {
        // Check queue size limit
        if ($this->queueSize >= $this->maxQueueSize) {
            if ($this->enableBackpressure) {
                $this->droppedTasks++;
                throw new RuntimeException("Task queue full, task dropped");
            }
        }

        // Apply backpressure
        if (
            $this->enableBackpressure &&
            $this->queueSize >= $this->backpressureThreshold
        ) {
            usleep(500); // Reduced delay to 0.5ms
        }

        $task = CallableUtil::makeAllToCallable($task);
        $task = $this->taskPool->getTask($task, $context);
        $this->readyQueue->insert($task, -$task->id);
        $this->queueSize++;

        return $task->id;
    }

    /**
     * Start the event loop and begin processing tasks.
     *
     * This is the main execution method that runs the event loop until all
     * tasks are completed or the loop is explicitly closed. It continuously
     * processes ready tasks, running tasks, and deferred tasks while managing
     * memory usage and applying execution limits.
     *
     * The loop will continue running while there are:
     * - Tasks in the ready queue
     * - Currently running tasks
     * - Deferred tasks waiting to execute
     *
     * @return void
     */
    public function run(): void
    {
        $this->startTime = microtime(true);
        $this->isRunning = true;

        while (
            $this->queueSize > 0 ||
            !empty($this->runningTasks) ||
            !empty($this->deferredTasks)
        ) {
            if (!$this->isRunning) {
                break;
            }

            $this->resetCycleCounters();
            $this->processTasksWithLimits();
            $this->handleMemoryManagement();

            // Yield control only when necessary
            if ($this->shouldYieldControl()) {
                usleep(50); // Reduced to 0.05ms for less overhead
            }

            if ($this->isLimitedToIterations()) {
                break;
            }
        }

        $this->memoryManager?->collectGarbage();
    }

    /**
     * Reset the cycle counters for a new execution cycle.
     *
     * Initializes the task count and start time for the current execution
     * cycle. This is called at the beginning of each cycle to ensure
     * accurate tracking of cycle limits.
     *
     * @return void
     */
    private function resetCycleCounters(): void
    {
        $this->currentCycleTaskCount = 0;
        $this->cycleStartTime = microtime(true);
    }

    /**
     * Process tasks with various limits and constraints.
     *
     * Handles both ready tasks from the queue and currently running tasks,
     * respecting cycle limits, execution time limits, and iteration limits.
     * This method ensures the event loop doesn't overwhelm the system by
     * processing too many tasks in a single cycle.
     *
     * @return void
     */
    private function processTasksWithLimits(): void
    {
        // Process ready tasks
        while ($this->queueSize > 0 && $this->canProcessMoreTasks()) {
            $task = $this->readyQueue->extract();
            $this->queueSize--;
            $this->executeTask($task);
            $this->currentCycleTaskCount++;

            if (!$this->canContinueIteration()) {
                break;
            }
        }

        // Process running tasks
        $runningTasks = $this->runningTasks; // Cache to avoid modifying array during iteration
        foreach ($runningTasks as $task) {
            if (!$this->canProcessMoreTasks()) {
                break;
            }

            $this->executeTask($task);
            $this->currentCycleTaskCount++;

            if (!$this->canContinueIteration()) {
                break;
            }
        }
    }

    /**
     * Check if more tasks can be processed in the current cycle.
     *
     * Determines whether the event loop can continue processing tasks
     * based on the configured limits for maximum tasks per cycle and
     * maximum execution time per cycle.
     *
     * @return bool True if more tasks can be processed, false otherwise
     */
    private function canProcessMoreTasks(): bool
    {
        return $this->currentCycleTaskCount < $this->maxTasksPerCycle &&
            microtime(true) - $this->cycleStartTime < $this->maxExecutionTime;
    }

    /**
     * Check if the event loop should yield control to the system.
     *
     * Determines whether the current execution cycle has reached its
     * limits and should yield control to prevent blocking the system.
     * This is the inverse of canProcessMoreTasks().
     *
     * @return bool True if control should be yielded, false otherwise
     */
    private function shouldYieldControl(): bool
    {
        return $this->currentCycleTaskCount >= $this->maxTasksPerCycle ||
            microtime(true) - $this->cycleStartTime >= $this->maxExecutionTime;
    }

    /**
     * Handle memory management and cleanup operations.
     *
     * Checks current memory usage and triggers garbage collection if
     * necessary. Also cleans up empty deferred task arrays to prevent
     * memory leaks from completed tasks.
     *
     * @return void
     */
    private function handleMemoryManagement(): void
    {
        if ($this->memoryManager?->checkMemoryUsage()) {
            $this->memoryManager->forceGarbageCollection();
            // Clear unused deferred tasks
            $this->deferredTasks = array_filter(
                $this->deferredTasks,
                fn($tasks) => !empty($tasks)
            );
        }
    }

    /**
     * Get the current size of the ready task queue.
     *
     * Returns the cached queue size to avoid repeated expensive operations
     * on the SplPriorityQueue. The cached size is maintained throughout
     * the lifecycle of queue operations.
     *
     * @return int The number of tasks currently in the ready queue
     */
    private function getQueueSize(): int
    {
        return $this->queueSize;
    }

    /**
     * Close the event loop and stop task processing.
     *
     * Gracefully shuts down the event loop by setting the running flag to false
     * and clearing the task queue. This will cause the run() method to exit
     * on the next iteration.
     *
     * @return void
     */
    public function close(): void
    {
        $this->isRunning = false;
        $this->queueSize = 0; // Reset cached size
        $this->readyQueue = new SplPriorityQueue(); // Clear queue
    }

    /**
     * Set the maximum number of tasks to process per execution cycle.
     *
     * This setting controls how many tasks can be processed in a single
     * execution cycle before yielding control. Higher values increase
     * throughput but may cause longer blocking periods.
     *
     * @param int $maxTasks Maximum tasks per cycle (must be positive)
     * @return void
     * @throws InvalidArgumentException If maxTasks is not positive
     */
    public function setMaxTasksPerCycle(int $maxTasks): void
    {
        if ($maxTasks <= 0) {
            throw new InvalidArgumentException(
                "Max tasks per cycle must be positive"
            );
        }
        $this->maxTasksPerCycle = $maxTasks;
    }

    /**
     * Set the maximum size of the task queue.
     *
     * Controls the maximum number of tasks that can be queued before
     * backpressure mechanisms are applied. The backpressure threshold
     * is automatically updated to 80% of the max size.
     *
     * @param int $maxSize Maximum queue size (must be positive)
     * @return void
     * @throws InvalidArgumentException If maxSize is not positive
     */
    public function setMaxQueueSize(int $maxSize): void
    {
        if ($maxSize <= 0) {
            throw new InvalidArgumentException(
                "Max queue size must be positive"
            );
        }
        $this->maxQueueSize = $maxSize;
        $this->backpressureThreshold = (int) ($maxSize * 0.8); // Update threshold
    }

    /**
     * Set the maximum execution time per cycle in seconds.
     *
     * Limits how long a single execution cycle can run before yielding
     * control to prevent blocking. This ensures responsiveness even
     * with computationally intensive tasks.
     *
     * @param float $maxTime Maximum execution time in seconds (must be positive)
     * @return void
     * @throws InvalidArgumentException If maxTime is not positive
     */
    public function setMaxExecutionTime(float $maxTime): void
    {
        if ($maxTime <= 0) {
            throw new InvalidArgumentException(
                "Max execution time must be positive"
            );
        }
        $this->maxExecutionTime = $maxTime;
    }

    /**
     * Enable or disable backpressure control.
     *
     * When enabled, backpressure mechanisms will apply delays and potentially
     * drop tasks when the queue size approaches its limits. This helps prevent
     * memory exhaustion under high load.
     *
     * @param bool $enabled Whether to enable backpressure control
     * @return void
     */
    public function setBackpressureEnabled(bool $enabled): void
    {
        $this->enableBackpressure = $enabled;
    }

    /**
     * Set the backpressure threshold for queue size.
     *
     * When the queue size reaches this threshold, backpressure mechanisms
     * will be applied (delays, warnings, etc.) to prevent overwhelming
     * the system. Must be less than or equal to the max queue size.
     *
     * @param int $threshold Backpressure threshold (must be positive and <= max queue size)
     * @return void
     * @throws InvalidArgumentException If threshold is invalid
     */
    public function setBackpressureThreshold(int $threshold): void
    {
        if ($threshold <= 0 || $threshold > $this->maxQueueSize) {
            throw new InvalidArgumentException(
                "Invalid backpressure threshold"
            );
        }
        $this->backpressureThreshold = $threshold;
    }

    /**
     * Set a limit on the number of iterations the event loop will run.
     *
     * Enables iteration limiting and sets the maximum number of iterations
     * before the loop stops. Useful for testing or controlled execution
     * scenarios.
     *
     * @param int $limit Maximum number of iterations (must be positive)
     * @return void
     * @throws InvalidArgumentException If limit is not positive
     */
    public function setIterationLimit(int $limit): void
    {
        if ($limit <= 0) {
            throw new InvalidArgumentException(
                "Iteration limit must be positive"
            );
        }
        $this->enableIterationLimit = true;
        $this->iterationLimit = $limit;
    }

    /**
     * Reset and disable the iteration limit.
     *
     * Disables iteration limiting and resets the limit back to the default
     * value, allowing the event loop to run indefinitely until all tasks
     * are completed.
     *
     * @return void
     */
    public function resetIterationLimit(): void
    {
        $this->enableIterationLimit = false;
        $this->iterationLimit = 1; // Reset to default
        $this->currentIteration = 0;
    }

    /**
     * Reset the current iteration counter to zero.
     *
     * Resets the iteration counter without changing the iteration limit,
     * effectively restarting the iteration count for the current run.
     *
     * @return void
     */
    public function resetIteration(): void
    {
        $this->currentIteration = 0;
    }

    /**
     * Check if the event loop can continue with more iterations.
     *
     * Returns true if iteration limiting is disabled or if the current
     * iteration count is below the limit. Updates the iteration counter
     * when checking.
     *
     * @return bool True if more iterations are allowed, false otherwise
     */
    public function canContinueIteration(): bool
    {
        if ($this->enableIterationLimit) {
            if ($this->currentIteration >= $this->iterationLimit) {
                return false;
            }
            $this->currentIteration++;
        }
        return true;
    }

    /**
     * Check if the event loop is limited by iteration count.
     *
     * Returns true if iteration limiting is enabled and the current
     * iteration count has reached or exceeded the limit.
     *
     * @return bool True if iteration limit has been reached, false otherwise
     */
    public function isLimitedToIterations(): bool
    {
        return $this->enableIterationLimit &&
            $this->currentIteration >= $this->iterationLimit;
    }

    /**
     * Get comprehensive statistics about the event loop's current state.
     *
     * Returns detailed information about the event loop including queue
     * sizes, task counts, memory usage, and performance metrics. Useful
     * for monitoring and debugging.
     *
     * @return array Associative array containing various statistics:
     *               - queue_size: Number of tasks in ready queue
     *               - running_tasks: Number of currently running tasks
     *               - deferred_tasks: Number of deferred task groups
     *               - dropped_tasks: Number of tasks dropped due to backpressure
     *               - task_pool_stats: Statistics from the task pool
     *               - memory_usage: Current memory usage in bytes
     *               - peak_memory: Peak memory usage in bytes
     */
    public function getStats(): array
    {
        return [
            "queue_size" => $this->queueSize,
            "running_tasks" => count($this->runningTasks),
            "deferred_tasks" => count($this->deferredTasks),
            "dropped_tasks" => $this->droppedTasks,
            "task_pool_stats" => $this->taskPool->getStats(),
            "memory_usage" => memory_get_usage(true),
            "peak_memory" => memory_get_peak_usage(true),
        ];
    }

    /**
     * Check if there are any ready tasks in the queue.
     *
     * @return bool True if there are tasks ready to execute, false otherwise
     */
    private function hasReadyTasks(): bool
    {
        return $this->queueSize > 0;
    }

    /**
     * Check if there are any currently running tasks.
     *
     * @return bool True if there are tasks currently executing, false otherwise
     */
    private function hasRunningTasks(): bool
    {
        return !empty($this->runningTasks);
    }

    /**
     * Execute a single task and handle its lifecycle.
     *
     * This method manages the complete execution lifecycle of a task including:
     * - State transitions (PENDING -> RUNNING -> COMPLETED/FAILED)
     * - Generator advancement and completion detection
     * - Special instruction handling (Sleep, Interval, Defer, CancelFuture)
     * - Error handling and task cleanup
     * - Result propagation through JoinHandle
     *
     * @param Task $task The task to execute
     * @return void
     */
    private function executeTask(Task $task): void
    {
        $result = null;
        $isDone = false;

        try {
            if ($task->state === TaskState::PENDING) {
                $task->state = TaskState::RUNNING;
                $this->runningTasks[$task->id] = $task;
                $task->callback = ($task->callback)($task->context, $this);
            }

            if ($task->state === TaskState::RUNNING) {
                if ($task->callback instanceof Generator) {
                    if (!$task->firstRun) {
                        $task->firstRun = true;
                    } else {
                        if ($task->callback->valid()) {
                            $task->callback->next();
                        }
                    }

                    if (!$task->callback->valid()) {
                        $isDone = true;
                        $result = GeneratorUtil::getReturnSafe($task->callback);
                        $this->completeTask($task, $result);
                    } else {
                        $current = $task->callback->current();
                        if ($current instanceof CancelFuture) {
                            $isDone = true;
                            $result = GeneratorUtil::getReturnSafe(
                                $task->callback
                            );
                            $this->completeTask($task, $result);
                        } elseif (
                            $current instanceof Sleep ||
                            $current instanceof Interval
                        ) {
                            $task->sleep($current->seconds);
                        } elseif ($current instanceof Defer) {
                            $this->deferredTasks[$task->id][] = $current;
                        }
                    }
                } else {
                    $isDone = true;
                    $this->completeTask($task, $task->callback);
                }
            } elseif ($task->state === TaskState::SLEEPING) {
                $task->tryWake();
            }
        } catch (Throwable $e) {
            $isDone = true;
            $result = $e;
            $this->failTask($task, $e);
        } finally {
            if ($isDone) {
                unset($this->runningTasks[$task->id]);
                JoinHandle::done($task->id, $result);
            }
        }
    }

    /**
     * Complete a task successfully and handle cleanup.
     *
     * Marks the task as completed, stores the result, returns the task
     * to the pool for reuse, and executes any deferred callbacks
     * associated with the task.
     *
     * @param Task $task The task to complete
     * @param mixed $result The result value from the task execution
     * @return void
     */
    private function completeTask(Task $task, mixed $result = null): void
    {
        $task->state = TaskState::COMPLETED;
        $task->result = $result;
        $this->taskPool->returnTask($task);

        if (!empty($this->deferredTasks[$task->id])) {
            foreach ($this->deferredTasks[$task->id] as $deferredTask) {
                ($deferredTask->callback)($result);
            }
            unset($this->deferredTasks[$task->id]);
        }
    }

    /**
     * Mark a task as failed and handle cleanup.
     *
     * Sets the task state to failed, stores the error, returns the task
     * to the pool, and cleans up any associated deferred tasks since
     * they won't be executed for failed tasks.
     *
     * @param Task $task The task that failed
     * @param Throwable $error The error that caused the task to fail
     * @return void
     */
    private function failTask(Task $task, Throwable $error): void
    {
        $task->state = TaskState::FAILED;
        $task->error = $error;
        $this->taskPool->returnTask($task);
        unset($this->deferredTasks[$task->id]);
    }
}
