***

# JoinSet

JoinSet - A collection of spawned tasks that can be awaited together.

It supports:
- Spawning tasks and adding them to the set
- Waiting for the next task to complete
- Waiting for all tasks to complete
- Aborting all tasks
- Checking if the set is empty
- Detaching tasks from the set

All operations use Generators for non-blocking execution and follow
the project's Result/Option patterns for error handling.

* Full name: `\venndev\vosaka\task\JoinSet`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### tasks



```php
private array $tasks
```






***

### completedResults



```php
private array $completedResults
```






***

### nextTaskId



```php
private int $nextTaskId
```






***

### aborted



```php
private bool $aborted
```






***

## Methods


### __construct

Create a new empty JoinSet

```php
public __construct(): mixed
```












***

### new

Create a new JoinSet (factory method)

```php
public static new(): self
```



* This method is **static**.








***

### spawn

Spawn a task and add it to the JoinSet

```php
public spawn(callable|\Generator $task, mixed $context = null): \venndev\vosaka\core\Result&lt;int&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$task` | **callable&#124;\Generator** | The task to spawn |
| `$context` | **mixed** | Optional context data |


**Return Value:**

Returns the task ID




***

### spawnWithKey

Spawn a task with a specific key/identifier

```php
public spawnWithKey(mixed $key, callable|\Generator $task, mixed $context = null): \venndev\vosaka\core\Result&lt;int&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$key` | **mixed** | The key to associate with the task |
| `$task` | **callable&#124;\Generator** | The task to spawn |
| `$context` | **mixed** | Optional context data |


**Return Value:**

Returns the task ID




***

### joinNext

Wait for the next task to complete and return its result

```php
public joinNext(): \venndev\vosaka\core\Result&lt;\venndev\vosaka\core\interfaces\Option&gt;
```









**Return Value:**

Returns Some([taskId, result]) or None if empty




***

### joinNextWithKey

Wait for the next task to complete with a key and return its result

```php
public joinNextWithKey(): \venndev\vosaka\core\Result&lt;\venndev\vosaka\core\interfaces\Option&gt;
```









**Return Value:**

Returns Some([key, taskId, result]) or None if empty




***

### joinAll

Wait for all tasks to complete and return their results

```php
public joinAll(): \venndev\vosaka\core\Result&lt;array&gt;
```









**Return Value:**

Returns array of [taskId => result]




***

### tryJoinNext

Try to join the next task without waiting

```php
public tryJoinNext(): \venndev\vosaka\core\interfaces\Option
```









**Return Value:**

Returns Some([taskId, result]) or None if no task is ready




***

### abortAll

Abort all tasks in the JoinSet

```php
public abortAll(): \venndev\vosaka\core\Result&lt;int&gt;
```









**Return Value:**

Returns the number of tasks that were aborted




***

### abort

Abort a specific task by ID

```php
public abort(int $taskId): \venndev\vosaka\core\Result&lt;bool&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$taskId` | **int** | The task ID to abort |


**Return Value:**

Returns true if task was found and aborted




***

### detach

Detach a task from the JoinSet (let it run but don't track it)

```php
public detach(int $taskId): bool
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$taskId` | **int** | The task ID to detach |


**Return Value:**

Returns true if task was found and detached




***

### isEmpty

Check if the JoinSet is empty

```php
public isEmpty(): bool
```









**Return Value:**

True if no tasks are being tracked




***

### len

Get the number of tasks currently in the JoinSet

```php
public len(): int
```









**Return Value:**

The number of active tasks




***

### clear

Clear all tasks from the JoinSet without aborting them

```php
public clear(): int
```









**Return Value:**

The number of tasks that were detached




***

### taskIds

Get all task IDs currently in the JoinSet

```php
public taskIds(): int[]
```









**Return Value:**

Array of task IDs




***

### contains

Check if a specific task ID exists in the JoinSet

```php
public contains(int $taskId): bool
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$taskId` | **int** | The task ID to check |


**Return Value:**

True if the task exists




***

### monitorTask

Monitor a task and handle its completion

```php
private monitorTask(\venndev\vosaka\task\JoinSetTask $joinSetTask): \Generator
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$joinSetTask` | **\venndev\vosaka\task\JoinSetTask** | The task to monitor |





***


***
> Automatically generated on 2025-07-16
