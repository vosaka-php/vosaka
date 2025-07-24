***

# TaskPool





* Full name: `\venndev\vosaka\eventloop\task\TaskPool`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### availableTasks



```php
private \SplQueue $availableTasks
```






***

### allTasks



```php
private \WeakMap $allTasks
```






***

### maxPoolSize



```php
private int $maxPoolSize
```






***

### created



```php
private int $created
```






***

### reused



```php
private int $reused
```






***

### currentPoolSize



```php
private int $currentPoolSize
```






***

## Methods


### __construct



```php
public __construct(int $maxPoolSize = 1000): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$maxPoolSize` | **int** |  |





***

### getTask



```php
public getTask(callable $callback, mixed $context = null): \venndev\vosaka\eventloop\task\Task
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$callback` | **callable** |  |
| `$context` | **mixed** |  |





***

### returnTask



```php
public returnTask(\venndev\vosaka\eventloop\task\Task $task): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$task` | **\venndev\vosaka\eventloop\task\Task** |  |





***

### getStats



```php
public getStats(): array
```












***

### clear



```php
public clear(): void
```












***

### warmUp

Warm up the task pool by creating a number of tasks.

```php
public warmUp(int|null $count = null): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$count` | **int&#124;null** | The number of tasks to create. Defaults to 100 or max pool size. |





***


***
> Automatically generated on 2025-07-24
