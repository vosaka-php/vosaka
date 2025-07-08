***

# TaskManager

This class focuses on task management and execution.



* Full name: `\venndev\vosaka\eventloop\task\TaskManager`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### taskPool



```php
private \venndev\vosaka\eventloop\task\TaskPool $taskPool
```






***

### runningTasks



```php
private \SplQueue $runningTasks
```






***

### deferredTasks



```php
private \WeakMap $deferredTasks
```






***

## Methods


### __construct



```php
public __construct(): mixed
```












***

### spawn

Spawn method with fast path for common cases

```php
public spawn(callable|\Generator $task, mixed $context = null): int
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$task` | **callable&#124;\Generator** |  |
| `$context` | **mixed** |  |





***

### processRunningTasks

Process running tasks

```php
public processRunningTasks(): void
```












***

### executeTask

Task execution with reduced overhead

```php
private executeTask(\venndev\vosaka\eventloop\task\Task $task): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$task` | **\venndev\vosaka\eventloop\task\Task** |  |





***

### handleGenerator

Generator handling with match expression

```php
private handleGenerator(\venndev\vosaka\eventloop\task\Task $task): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$task` | **\venndev\vosaka\eventloop\task\Task** |  |





***

### addDeferredTask

Deferred task addition with pooling

```php
private addDeferredTask(\venndev\vosaka\eventloop\task\Task $task, \venndev\vosaka\utils\Defer $defer): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$task` | **\venndev\vosaka\eventloop\task\Task** |  |
| `$defer` | **\venndev\vosaka\utils\Defer** |  |





***

### completeTask

Task completion with pooled arrays

```php
private completeTask(\venndev\vosaka\eventloop\task\Task $task, mixed $result = null): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$task` | **\venndev\vosaka\eventloop\task\Task** |  |
| `$result` | **mixed** |  |





***

### failTask



```php
private failTask(\venndev\vosaka\eventloop\task\Task $task, \Throwable $error): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$task` | **\venndev\vosaka\eventloop\task\Task** |  |
| `$error` | **\Throwable** |  |





***

### hasRunningTasks



```php
public hasRunningTasks(): bool
```












***

### getRunningTasksCount



```php
public getRunningTasksCount(): int
```












***

### getDeferredTasksCount



```php
public getDeferredTasksCount(): int
```












***

### getTaskPoolStats



```php
public getTaskPoolStats(): array
```












***

### reset



```php
public reset(): void
```












***


***
> Automatically generated on 2025-07-08
