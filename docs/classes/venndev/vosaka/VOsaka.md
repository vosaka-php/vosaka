***

# VOsaka

VOsaka - Main entry point for the asynchronous runtime system.

This class provides the primary API for creating, managing, and executing
asynchronous tasks using generators. It serves as a facade over the event
loop and provides high-level operations for:

- Spawning individual tasks with spawn()
- Joining multiple tasks with join()
- Trying to join tasks with error handling via tryJoin()
- Selecting the first completed task with select()
- Retrying failed tasks with configurable backoff
- Running the event loop and managing its lifecycle
- And more...

All task operations return Result objects that can be awaited using
generator-based coroutines, enabling non-blocking asynchronous execution.

* Full name: `\venndev\vosaka\VOsaka`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### eventLoop



```php
private static \venndev\vosaka\eventloop\EventLoop $eventLoop
```



* This property is **static**.


***

### taskCounter



```php
private static int $taskCounter
```



* This property is **static**.


***

## Methods


### getLoop

Get the singleton EventLoop instance.

```php
public static getLoop(): \venndev\vosaka\eventloop\EventLoop
```

Returns the global event loop instance, creating it if it doesn't exist.
This ensures all VOsaka operations share the same event loop for
coordinated task execution.

* This method is **static**.





**Return Value:**

The global event loop instance




***

### spawn

Spawn an asynchronous task and return a Result for awaiting completion.

```php
public static spawn(callable|\Generator $task, mixed $context = null): \venndev\vosaka\core\Result
```

Creates a new asynchronous task from the provided callable or generator
and schedules it for execution in the event loop. The task will run
concurrently with other tasks and can be awaited using the returned Result.

* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$task` | **callable&#124;\Generator** | The task to spawn (callable or generator) |
| `$context` | **mixed** | Optional context data passed to the task |


**Return Value:**

A Result object that can be used to await the task's completion




***

### join

Join multiple tasks and wait for all of them to complete.

```php
public static join(callable|\Generator|\venndev\vosaka\core\Result $tasks): \venndev\vosaka\core\Result
```

Executes multiple tasks concurrently and waits for all of them to finish.
Returns an array containing the results of all tasks in the same order
they were provided. If any task fails, the entire join operation fails.

This is the main entry point for concurrent task execution when you need
all tasks to complete successfully.

* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$tasks` | **callable&#124;\Generator&#124;\venndev\vosaka\core\Result** | The tasks to join and wait for |


**Return Value:**

A Result containing an array of all task results




***

### tryJoin

Try to join multiple tasks with error handling.

```php
public static tryJoin(callable|\Generator|\venndev\vosaka\core\Result $tasks): \venndev\vosaka\core\Result
```

Similar to join() but provides graceful error handling. If any task fails,
returns the error instead of throwing an exception. Returns null if all
tasks complete successfully, making it easy to check for success.

This is useful when you want to handle task failures explicitly rather
than having them propagate as exceptions.

* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$tasks` | **callable&#124;\Generator&#124;\venndev\vosaka\core\Result** | The tasks to try joining |


**Return Value:**

A Result containing null on success or the error on failure




***

### select

Select the first task that completes.

```php
public static select(callable|\Generator|\venndev\vosaka\core\Result $tasks): \venndev\vosaka\core\Result
```

Executes multiple tasks concurrently and returns as soon as the first
one completes. The result is a tuple [index, result] where index is
the position of the completed task and result is the value it returned.

This is useful for implementing timeouts, racing multiple operations,
or handling whichever operation completes first.

* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$tasks` | **callable&#124;\Generator&#124;\venndev\vosaka\core\Result** | The tasks to race |


**Return Value:**

A Result containing [index, result] of the first completed task




***

### selectTimeout

Select the first task that completes within a timeout period.

```php
public static selectTimeout(float $timeoutSeconds, callable|\Generator|\venndev\vosaka\core\Result $tasks): \venndev\vosaka\core\Result
```

Similar to select() but with a timeout mechanism. Returns the first task
that completes within the specified timeout, or null if the timeout is
reached before any task completes. The timeout task is automatically
added to the selection.

* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$timeoutSeconds` | **float** | Maximum time to wait in seconds |
| `$tasks` | **callable&#124;\Generator&#124;\venndev\vosaka\core\Result** | The tasks to race with timeout |


**Return Value:**

A Result containing [index, result] or null if timeout




***

### selectBiased

Select with biased ordering - tasks are checked in priority order.

```php
public static selectBiased(callable|\Generator|\venndev\vosaka\core\Result $tasks): \venndev\vosaka\core\Result
```

Similar to select() but with deterministic ordering. Tasks are checked
in the order they were provided, giving earlier tasks higher priority
when multiple tasks are ready simultaneously. This ensures predictable
behavior in scenarios where task completion order matters.

* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$tasks` | **callable&#124;\Generator&#124;\venndev\vosaka\core\Result** | The tasks to select from (in priority order) |


**Return Value:**

A Result containing [index, result] of the first completed task




***

### processAllTasks



```php
private static processAllTasks(callable|\Generator|\venndev\vosaka\core\Result $tasks): \Generator
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$tasks` | **callable&#124;\Generator&#124;\venndev\vosaka\core\Result** |  |





***

### processSelectTasks

Process tasks for select operation - returns first completed task with its index.

```php
private static processSelectTasks(callable|\Generator|\venndev\vosaka\core\Result $tasks): \Generator
```

Internal method that handles the concurrent execution and monitoring of
tasks for the select() operation. Continuously polls all tasks until
one completes, then returns its index and result.

* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$tasks` | **callable&#124;\Generator&#124;\venndev\vosaka\core\Result** | The tasks to process |


**Return Value:**

A generator that yields [index, result] of first completed task



**Throws:**
<p>If no tasks are provided</p>

- [`InvalidArgumentException`](../../InvalidArgumentException.md)
<p>If all tasks complete unexpectedly</p>

- [`RuntimeException`](../../RuntimeException.md)



***

### processSelectTasksBiased

Process tasks with biased ordering (check tasks in priority order).

```php
private static processSelectTasksBiased(callable|\Generator|\venndev\vosaka\core\Result $tasks): \Generator
```

Internal method for selectBiased() that implements deterministic task
checking. Tasks are always checked in the order provided, ensuring
earlier tasks have priority when multiple are ready.

* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$tasks` | **callable&#124;\Generator&#124;\venndev\vosaka\core\Result** | The tasks to process in order |


**Return Value:**

A generator that yields [index, result] of first completed task



**Throws:**
<p>If no tasks are provided</p>

- [`InvalidArgumentException`](../../InvalidArgumentException.md)
<p>If all tasks complete unexpectedly</p>

- [`RuntimeException`](../../RuntimeException.md)



***

### processOneIndexedTasks

Legacy method - kept for backward compatibility

```php
private static processOneIndexedTasks(callable|\Generator|\venndev\vosaka\core\Result $tasks): \Generator
```



* This method is **static**.


* **Warning:** this method is **deprecated**. This means that this method will likely be removed in a future version.



**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$tasks` | **callable&#124;\Generator&#124;\venndev\vosaka\core\Result** |  |





***

### retry

Retry a task with configurable retry logic and exponential backoff.

```php
public static retry(callable $taskFactory, int $maxRetries = 3, int $delaySeconds = 1, int $backOffMultiplier = 2, callable|null $shouldRetry = null): \venndev\vosaka\core\Result
```

Executes a task factory function with automatic retry on failure.
Supports configurable retry count, delay between attempts, exponential
backoff multiplier, and custom retry condition logic.

The task factory should return a Generator that represents the task
to be executed. If the task fails, it will be retried according to
the specified parameters.

* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$taskFactory` | **callable** | Factory function that returns a Generator task |
| `$maxRetries` | **int** | Maximum number of retry attempts (default: 3) |
| `$delaySeconds` | **int** | Initial delay between retries in seconds (default: 1) |
| `$backOffMultiplier` | **int** | Multiplier for exponential backoff (default: 2) |
| `$shouldRetry` | **callable&#124;null** | Optional predicate to determine if retry should occur |


**Return Value:**

A Result containing the task result or final failure



**Throws:**
<p>If task factory doesn't return a Generator</p>

- [`InvalidArgumentException`](../../InvalidArgumentException.md)
<p>If all retries are exhausted</p>

- [`RuntimeException`](../../RuntimeException.md)



***

### run

Start the event loop and run until all tasks complete.

```php
public static run(): void
```

Begins execution of the event loop, which will continue running until
all spawned tasks have completed or the loop is explicitly closed.
This is typically called once at the end of your program to start
the asynchronous runtime.

* This method is **static**.








***

### close

Close the event loop and stop all task processing.

```php
public static close(): void
```

Gracefully shuts down the event loop, stopping all task processing
and cleaning up resources. This will cause the run() method to exit
and should be called when you want to terminate the asynchronous runtime.

* This method is **static**.








***


***
> Automatically generated on 2025-07-08
