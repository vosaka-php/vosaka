***

# EventLoop

EventLoop class manages the asynchronous task execution runtime.

This is the core of the VOsaka asynchronous runtime, responsible for:
- Spawning and managing asynchronous tasks
- Handling task scheduling and execution
- Managing memory usage and garbage collection
- Providing backpressure control and queue size limits
- Graceful shutdown and cleanup operations

The EventLoop uses a priority queue to manage ready tasks and maintains
separate collections for running and deferred tasks. It implements
various performance optimizations including cycle limits, execution
time limits, and memory management.

* Full name: `\venndev\vosaka\runtime\eventloop\EventLoop`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### readyQueue



```php
private \SplPriorityQueue $readyQueue
```






***

### taskPool



```php
private \venndev\vosaka\runtime\eventloop\task\TaskPool $taskPool
```






***

### memoryManager



```php
private ?\venndev\vosaka\core\MemoryManager $memoryManager
```






***

### gracefulShutdown



```php
private ?\venndev\vosaka\cleanup\GracefulShutdown $gracefulShutdown
```






***

### runningTasks



```php
private array $runningTasks
```






***

### deferredTasks



```php
private array $deferredTasks
```






***

### isRunning



```php
private bool $isRunning
```






***

### maxMemoryUsage



```php
private int $maxMemoryUsage
```






***

### taskProcessedCount



```php
private int $taskProcessedCount
```






***

### startTime



```php
private float $startTime
```






***

### maxTasksPerCycle



```php
private int $maxTasksPerCycle
```






***

### maxQueueSize



```php
private int $maxQueueSize
```






***

### maxExecutionTime



```php
private float $maxExecutionTime
```






***

### currentCycleTaskCount



```php
private int $currentCycleTaskCount
```






***

### cycleStartTime



```php
private float $cycleStartTime
```






***

### enableBackpressure



```php
private bool $enableBackpressure
```






***

### backpressureThreshold



```php
private int $backpressureThreshold
```






***

### droppedTasks



```php
private int $droppedTasks
```






***

### iterationLimit



```php
private int $iterationLimit
```






***

### currentIteration



```php
private int $currentIteration
```






***

### enableIterationLimit



```php
private bool $enableIterationLimit
```






***

### queueSize



```php
private int $queueSize
```






***

## Methods


### __construct

Constructor for EventLoop.

```php
public __construct(int $maxMemoryMB = 128): mixed
```

Initializes the event loop with configurable memory limits and sets up
the task management infrastructure including the ready queue and task pool.






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$maxMemoryMB` | **int** | Maximum memory usage in megabytes (default: 128MB) |





***

### getMemoryManager

Get or create the memory manager instance.

```php
public getMemoryManager(): \venndev\vosaka\core\MemoryManager
```

Returns a singleton MemoryManager instance that monitors and controls
memory usage within the specified limits. Creates the instance on first
access using lazy initialization.







**Return Value:**

The memory manager instance




***

### getGracefulShutdown

Get or create the graceful shutdown manager instance.

```php
public getGracefulShutdown(): \venndev\vosaka\cleanup\GracefulShutdown
```

Returns a singleton GracefulShutdown instance that handles cleanup
operations and temporary file management during shutdown. Creates
the instance on first access using lazy initialization.







**Return Value:**

The graceful shutdown manager instance




***

### spawn

Spawn a new asynchronous task in the event loop.

```php
public spawn(callable|\Generator $task, mixed $context = null): int
```

Creates a new task from the provided callable or generator and adds it to
the ready queue for execution. The task will be executed asynchronously
as part of the event loop's task scheduling.






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$task` | **callable&#124;\Generator** | The task to spawn (callable or generator) |
| `$context` | **mixed** | Optional context data to pass to the task |


**Return Value:**

The unique task ID for tracking the spawned task



**Throws:**
<p>If the task queue is full and backpressure is enabled</p>

- [`RuntimeException`](../../../../RuntimeException.md)



***

### run

Start the event loop and begin processing tasks.

```php
public run(): void
```

This is the main execution method that runs the event loop until all
tasks are completed or the loop is explicitly closed. It continuously
processes ready tasks, running tasks, and deferred tasks while managing
memory usage and applying execution limits.

The loop will continue running while there are:
- Tasks in the ready queue
- Currently running tasks
- Deferred tasks waiting to execute










***

### resetCycleCounters

Reset the cycle counters for a new execution cycle.

```php
private resetCycleCounters(): void
```

Initializes the task count and start time for the current execution
cycle. This is called at the beginning of each cycle to ensure
accurate tracking of cycle limits.










***

### processTasksWithLimits

Process tasks with various limits and constraints.

```php
private processTasksWithLimits(): void
```

Handles both ready tasks from the queue and currently running tasks,
respecting cycle limits, execution time limits, and iteration limits.
This method ensures the event loop doesn't overwhelm the system by
processing too many tasks in a single cycle.










***

### canProcessMoreTasks

Check if more tasks can be processed in the current cycle.

```php
private canProcessMoreTasks(): bool
```

Determines whether the event loop can continue processing tasks
based on the configured limits for maximum tasks per cycle and
maximum execution time per cycle.







**Return Value:**

True if more tasks can be processed, false otherwise




***

### shouldYieldControl

Check if the event loop should yield control to the system.

```php
private shouldYieldControl(): bool
```

Determines whether the current execution cycle has reached its
limits and should yield control to prevent blocking the system.
This is the inverse of canProcessMoreTasks().







**Return Value:**

True if control should be yielded, false otherwise




***

### handleMemoryManagement

Handle memory management and cleanup operations.

```php
private handleMemoryManagement(): void
```

Checks current memory usage and triggers garbage collection if
necessary. Also cleans up empty deferred task arrays to prevent
memory leaks from completed tasks.










***

### getQueueSize

Get the current size of the ready task queue.

```php
private getQueueSize(): int
```

Returns the cached queue size to avoid repeated expensive operations
on the SplPriorityQueue. The cached size is maintained throughout
the lifecycle of queue operations.







**Return Value:**

The number of tasks currently in the ready queue




***

### close

Close the event loop and stop task processing.

```php
public close(): void
```

Gracefully shuts down the event loop by setting the running flag to false
and clearing the task queue. This will cause the run() method to exit
on the next iteration.










***

### setMaxTasksPerCycle

Set the maximum number of tasks to process per execution cycle.

```php
public setMaxTasksPerCycle(int $maxTasks): void
```

This setting controls how many tasks can be processed in a single
execution cycle before yielding control. Higher values increase
throughput but may cause longer blocking periods.






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$maxTasks` | **int** | Maximum tasks per cycle (must be positive) |




**Throws:**
<p>If maxTasks is not positive</p>

- [`InvalidArgumentException`](../../../../InvalidArgumentException.md)



***

### setMaxQueueSize

Set the maximum size of the task queue.

```php
public setMaxQueueSize(int $maxSize): void
```

Controls the maximum number of tasks that can be queued before
backpressure mechanisms are applied. The backpressure threshold
is automatically updated to 80% of the max size.






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$maxSize` | **int** | Maximum queue size (must be positive) |




**Throws:**
<p>If maxSize is not positive</p>

- [`InvalidArgumentException`](../../../../InvalidArgumentException.md)



***

### setMaxExecutionTime

Set the maximum execution time per cycle in seconds.

```php
public setMaxExecutionTime(float $maxTime): void
```

Limits how long a single execution cycle can run before yielding
control to prevent blocking. This ensures responsiveness even
with computationally intensive tasks.






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$maxTime` | **float** | Maximum execution time in seconds (must be positive) |




**Throws:**
<p>If maxTime is not positive</p>

- [`InvalidArgumentException`](../../../../InvalidArgumentException.md)



***

### setBackpressureEnabled

Enable or disable backpressure control.

```php
public setBackpressureEnabled(bool $enabled): void
```

When enabled, backpressure mechanisms will apply delays and potentially
drop tasks when the queue size approaches its limits. This helps prevent
memory exhaustion under high load.






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$enabled` | **bool** | Whether to enable backpressure control |





***

### setBackpressureThreshold

Set the backpressure threshold for queue size.

```php
public setBackpressureThreshold(int $threshold): void
```

When the queue size reaches this threshold, backpressure mechanisms
will be applied (delays, warnings, etc.) to prevent overwhelming
the system. Must be less than or equal to the max queue size.






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$threshold` | **int** | Backpressure threshold (must be positive and &lt;= max queue size) |




**Throws:**
<p>If threshold is invalid</p>

- [`InvalidArgumentException`](../../../../InvalidArgumentException.md)



***

### setIterationLimit

Set a limit on the number of iterations the event loop will run.

```php
public setIterationLimit(int $limit): void
```

Enables iteration limiting and sets the maximum number of iterations
before the loop stops. Useful for testing or controlled execution
scenarios.






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$limit` | **int** | Maximum number of iterations (must be positive) |




**Throws:**
<p>If limit is not positive</p>

- [`InvalidArgumentException`](../../../../InvalidArgumentException.md)



***

### resetIterationLimit

Reset and disable the iteration limit.

```php
public resetIterationLimit(): void
```

Disables iteration limiting and resets the limit back to the default
value, allowing the event loop to run indefinitely until all tasks
are completed.










***

### resetIteration

Reset the current iteration counter to zero.

```php
public resetIteration(): void
```

Resets the iteration counter without changing the iteration limit,
effectively restarting the iteration count for the current run.










***

### canContinueIteration

Check if the event loop can continue with more iterations.

```php
public canContinueIteration(): bool
```

Returns true if iteration limiting is disabled or if the current
iteration count is below the limit. Updates the iteration counter
when checking.







**Return Value:**

True if more iterations are allowed, false otherwise




***

### isLimitedToIterations

Check if the event loop is limited by iteration count.

```php
public isLimitedToIterations(): bool
```

Returns true if iteration limiting is enabled and the current
iteration count has reached or exceeded the limit.







**Return Value:**

True if iteration limit has been reached, false otherwise




***

### getStats

Get comprehensive statistics about the event loop's current state.

```php
public getStats(): array
```

Returns detailed information about the event loop including queue
sizes, task counts, memory usage, and performance metrics. Useful
for monitoring and debugging.







**Return Value:**

Associative array containing various statistics:
- queue_size: Number of tasks in ready queue
- running_tasks: Number of currently running tasks
- deferred_tasks: Number of deferred task groups
- dropped_tasks: Number of tasks dropped due to backpressure
- task_pool_stats: Statistics from the task pool
- memory_usage: Current memory usage in bytes
- peak_memory: Peak memory usage in bytes




***

### hasReadyTasks

Check if there are any ready tasks in the queue.

```php
private hasReadyTasks(): bool
```









**Return Value:**

True if there are tasks ready to execute, false otherwise




***

### hasRunningTasks

Check if there are any currently running tasks.

```php
private hasRunningTasks(): bool
```









**Return Value:**

True if there are tasks currently executing, false otherwise




***

### executeTask

Execute a single task and handle its lifecycle.

```php
private executeTask(\venndev\vosaka\runtime\eventloop\task\Task $task): void
```

This method manages the complete execution lifecycle of a task including:
- State transitions (PENDING -> RUNNING -> COMPLETED/FAILED)
- Generator advancement and completion detection
- Special instruction handling (Sleep, Interval, Defer, CancelFuture)
- Error handling and task cleanup
- Result propagation through JoinHandle






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$task` | **\venndev\vosaka\runtime\eventloop\task\Task** | The task to execute |





***

### completeTask

Complete a task successfully and handle cleanup.

```php
private completeTask(\venndev\vosaka\runtime\eventloop\task\Task $task, mixed $result = null): void
```

Marks the task as completed, stores the result, returns the task
to the pool for reuse, and executes any deferred callbacks
associated with the task.






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$task` | **\venndev\vosaka\runtime\eventloop\task\Task** | The task to complete |
| `$result` | **mixed** | The result value from the task execution |





***

### failTask

Mark a task as failed and handle cleanup.

```php
private failTask(\venndev\vosaka\runtime\eventloop\task\Task $task, \Throwable $error): void
```

Sets the task state to failed, stores the error, returns the task
to the pool, and cleans up any associated deferred tasks since
they won't be executed for failed tasks.






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$task` | **\venndev\vosaka\runtime\eventloop\task\Task** | The task that failed |
| `$error` | **\Throwable** | The error that caused the task to fail |





***


***
> Automatically generated on 2025-06-26
