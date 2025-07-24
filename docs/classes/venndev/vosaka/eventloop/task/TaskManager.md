***

# TaskManager

Optimized TaskManager with batch processing and performance improvements



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

### lastProcessedCount



```php
private int $lastProcessedCount
```






***

### deferredArrayPool



```php
private array $deferredArrayPool
```






***

### taskBatchPool



```php
private array $taskBatchPool
```






***

### maxBatchSize



```php
private int $maxBatchSize
```






***

## Methods


### __construct



```php
public __construct(): mixed
```












***

### initializePools

Initialize object pools for memory optimization

```php
private initializePools(): void
```












***

### getPooledDeferredArray

Get a pooled array for deferred tasks

```php
private getPooledDeferredArray(): array
```












***

### returnPooledDeferredArray

Return an array to the pool

```php
private returnPooledDeferredArray(array $arr): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$arr` | **array** |  |





***

### getPooledBatchArray

Get a pooled batch array

```php
private getPooledBatchArray(): array
```












***

### returnPooledBatchArray

Return batch array to pool

```php
private returnPooledBatchArray(array $arr): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$arr` | **array** |  |





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

Process running tasks with batch optimization

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

Optimized generator handling

```php
private handleGenerator(\venndev\vosaka\eventloop\task\Task $task): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$task` | **\venndev\vosaka\eventloop\task\Task** |  |





***

### addDeferredTask

Optimized deferred task addition with pooling

```php
private addDeferredTask(\venndev\vosaka\eventloop\task\Task $task, \venndev\vosaka\core\Defer $defer): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$task` | **\venndev\vosaka\eventloop\task\Task** |  |
| `$defer` | **\venndev\vosaka\core\Defer** |  |





***

### processDeferredTasks

Process deferred tasks efficiently

```php
private processDeferredTasks(\venndev\vosaka\eventloop\task\Task $task, mixed $result = null): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$task` | **\venndev\vosaka\eventloop\task\Task** |  |
| `$result` | **mixed** |  |





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

Task failure handling

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

### getLastProcessedCount



```php
public getLastProcessedCount(): int
```












***

### getTaskPoolStats



```php
public getTaskPoolStats(): array
```












***

### setMaxBatchSize



```php
public setMaxBatchSize(int $size): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$size` | **int** |  |





***

### reset



```php
public reset(): void
```












***


***
> Automatically generated on 2025-07-24
