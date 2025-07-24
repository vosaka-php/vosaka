***

# JoinHandle

JoinHandle class for tracking and waiting on asynchronous task completion.

This class provides a handle for tracking the execution state and result
of spawned asynchronous tasks. It implements a registry pattern using an
indexed array where each task gets a unique ID and corresponding JoinHandle
instance that can be used to wait for completion and retrieve results.

* Full name: `\venndev\vosaka\io\JoinHandle`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### result



```php
public mixed $result
```






***

### yieldData



```php
public mixed $yieldData
```






***

### done



```php
public bool $done
```






***

### justSpawned



```php
public bool $justSpawned
```






***

### instances

Registry of active JoinHandle instances indexed by task ID.

```php
private static array&lt;int,self&gt; $instances
```



* This property is **static**.


***

### id



```php
public int $id
```






***

## Methods


### __construct

Private constructor to prevent direct instantiation.

```php
public __construct(int $id): mixed
```

JoinHandle instances should only be created through the static
factory method new() to ensure proper registration and ID management.






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$id` | **int** | The unique task ID for this handle |





***

### tryYield

Attempt to yield data for a task with the given ID.

```php
public static tryYield(int $id, mixed $data): void
```

This method allows a task to yield data back to the event loop, which
can be used by other coroutines waiting on this task. The data is stored
in the JoinHandle instance associated with the task ID.

* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$id` | **int** | The unique task ID to yield data for |
| `$data` | **mixed** | The data to yield back to the event loop |




**Throws:**
<p>If no handle exists for the given ID</p>

- [`RuntimeException`](../../../RuntimeException.md)



***

### new

Create a new JoinHandle for tracking task completion.

```php
public static new(int $id): \venndev\vosaka\core\Result
```

Factory method that creates a new JoinHandle instance for the specified
task ID and registers it in the static array registry. Returns a
Result that can be awaited to get the task's final result.

If a handle with the same ID already exists and is still active,
it will be replaced. This allows for natural reuse of IDs across
different execution contexts (benchmarks, tests, etc.).

* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$id` | **int** | The unique task ID to track |


**Return Value:**

A Result that will resolve to the task's final result




***

### done

Mark a task as completed with the given result.

```php
public static done(int $id, mixed $result): void
```

Called by the event loop when a task completes (successfully or with
an error). Sets the result and marks the handle as done, which will
cause any waiting coroutines to receive the result.

The result is always stored in the handle for retrieval by waiting
coroutines. Errors are not thrown immediately - they are stored and
will be handled by the waiting coroutine in tryingDone().

Completed handles are NOT cleaned up here - they are cleaned up
when the waiting coroutine retrieves the result in tryingDone().

* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$id` | **int** | The task ID to mark as completed |
| `$result` | **mixed** | The result or error from the task |




**Throws:**
<p>If no handle exists for the given ID</p>

- [`RuntimeException`](../../../RuntimeException.md)



***

### isDone

Check if a task with the given ID has completed.

```php
public static isDone(int $id): bool
```

Returns true if the task has finished execution (either successfully
or with an error), false if it's still running or doesn't exist.

* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$id` | **int** | The task ID to check |


**Return Value:**

True if the task is completed, false otherwise




***

### tryingDone

Generator that waits for task completion and returns the result.

```php
private static tryingDone(self $handle): \Generator
```

Internal generator method that implements the waiting logic for task
completion. Marks the handle as no longer just spawned, then yields
control to the event loop until the task is marked as done.

Once the task completes, retrieves the result, cleans up the handle
from the registry, and returns the final result.

If the task just spawned and the result is an error, the error is
thrown here rather than in done() to avoid crashing the event loop.

* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$handle` | **self** | The JoinHandle to wait for |


**Return Value:**

A generator that yields the task's final result



**Throws:**
<p>If the task failed and was just spawned</p>

- [`\Throwable|\Error`](../../../Throwable|/Error.md)



***

### getActiveCount

Get the current number of active handles in the registry.

```php
public static getActiveCount(): int
```

Utility method for debugging and monitoring purposes.

* This method is **static**.





**Return Value:**

The number of active JoinHandle instances




***

### getActiveIds

Get all active task IDs.

```php
public static getActiveIds(): int[]
```

Utility method for debugging and monitoring purposes.

* This method is **static**.





**Return Value:**

Array of active task IDs




***

### clearAll

Clear all handles from the registry.

```php
public static clearAll(): void
```

Utility method for cleanup, primarily used in testing scenarios.
Use with caution in production as it may cause waiting tasks to hang.

* This method is **static**.








***


***
> Automatically generated on 2025-07-24
