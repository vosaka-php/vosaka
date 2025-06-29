***

# JoinHandle

JoinHandle class for tracking and waiting on asynchronous task completion.

This class provides a handle for tracking the execution state and result
of spawned asynchronous tasks. It implements a registry pattern using WeakMap
where each task gets a unique ID and corresponding JoinHandle instance that
can be used to wait for completion and retrieve results.

* Full name: `\venndev\vosaka\io\JoinHandle`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### result



```php
public mixed $result
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



```php
private static \WeakMap $instances
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
factory method c() to ensure proper registration and ID management.






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$id` | **int** | The unique task ID for this handle |





***

### c

Create a new JoinHandle for tracking task completion.

```php
public static c(int $id): \venndev\vosaka\core\Result
```

Factory method that creates a new JoinHandle instance for the specified
task ID and registers it in the static WeakMap registry. Returns a
Result that can be awaited to get the task's final result.

* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$id` | **int** | The unique task ID to track |


**Return Value:**

A Result that will resolve to the task's final result



**Throws:**
<p>If a handle with the same ID already exists</p>

- [`RuntimeException`](../../../RuntimeException.md)



***

### done

Mark a task as completed with the given result.

```php
public static done(int $id, mixed $result): void
```

Called by the event loop when a task completes (successfully or with
an error). Sets the result and marks the handle as done, which will
cause any waiting coroutines to receive the result.

If the task just spawned and produced an error, the error is thrown
immediately. Otherwise, the result is stored for later retrieval.
Completed handles are cleaned up from the WeakMap registry.

* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$id` | **int** | The task ID to mark as completed |
| `$result` | **mixed** | The result or error from the task |




**Throws:**
<p>If the task failed and was just spawned</p>

- [`\Throwable|\Error`](../../../Throwable|/Error.md)



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

### getInstance

Get a JoinHandle instance by ID from the WeakMap registry.

```php
private static getInstance(int $id): self
```

Internal method for retrieving JoinHandle instances from the static
WeakMap registry. Throws an exception if no handle exists for the given ID.

* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$id` | **int** | The task ID to retrieve |


**Return Value:**

The JoinHandle instance for the given ID



**Throws:**
<p>If no handle exists for the given ID</p>

- [`RuntimeException`](../../../RuntimeException.md)



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
from the WeakMap registry, and returns the final result.

* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$handle` | **self** | The JoinHandle to wait for |


**Return Value:**

A generator that yields the task's final result




***


***
> Automatically generated on 2025-06-29
