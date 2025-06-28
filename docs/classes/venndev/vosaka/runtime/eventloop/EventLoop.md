***

# EventLoop

EventLoop class for high-performance asynchronous task execution.

This enhanced version includes multiple performance optimizations:
- Batch processing for reduced overhead
- Memory pooling for object reuse
- Adaptive algorithms for smart resource management
- Micro-optimizations for hot paths
- Reduced method calls and improved caching

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
private \WeakMap $runningTasks
```






***

### deferredTasks



```php
private \WeakMap $deferredTasks
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

### hasRunningTasksCache



```php
private bool $hasRunningTasksCache
```






***

### hasDeferredTasksCache



```php
private bool $hasDeferredTasksCache
```






***

### cacheInvalidationCounter



```php
private int $cacheInvalidationCounter
```






***

### memoryCheckCounter



```php
private int $memoryCheckCounter
```






***

### memoryCheckInterval



```php
private int $memoryCheckInterval
```






***

### deferredArrayPool



```php
private array $deferredArrayPool
```






***

### batchTasksPool



```php
private array $batchTasksPool
```






***

### batchSize



```php
private int $batchSize
```






***

### yieldCounter



```php
private int $yieldCounter
```






***

## Methods


### __construct



```php
public __construct(int $maxMemoryMB = 128): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$maxMemoryMB` | **int** |  |





***

### initializePools

Initialize object pools for memory optimization

```php
private initializePools(): void
```












***

### getPooledArray

Get a pooled array for deferred tasks

```php
private getPooledArray(): array
```












***

### returnPooledArray

Return an array to the pool

```php
private returnPooledArray(array $arr): void
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

### getMemoryManager



```php
public getMemoryManager(): \venndev\vosaka\core\MemoryManager
```












***

### getGracefulShutdown



```php
public getGracefulShutdown(): \venndev\vosaka\cleanup\GracefulShutdown
```












***

### spawn

spawn method with fast path for common cases

```php
public spawn(callable|\Generator $task, mixed $context = null): int
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$task` | **callable&#124;\Generator** |  |
| `$context` | **mixed** |  |





***

### run

main run loop with batch processing and reduced overhead

```php
public run(): void
```












***

### hasWork

check for remaining work

```php
private hasWork(): bool
```












***

### hasRunningTasks

Cached check for running tasks

```php
private hasRunningTasks(): bool
```












***

### hasDeferredTasks

Cached check for deferred tasks

```php
private hasDeferredTasks(): bool
```












***

### fastWeakMapCount

Fast count for WeakMap with early exit

```php
private fastWeakMapCount(\WeakMap $map): int
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$map` | **\WeakMap** |  |





***

### processBatchTasks

Process tasks in batches for improved performance

```php
private processBatchTasks(): void
```












***

### processRunningTasks

Process running tasks

```php
private processRunningTasks(): void
```












***

### executeTask

Task execution with reduced overhead

```php
private executeTask(\venndev\vosaka\runtime\eventloop\task\Task $task): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$task` | **\venndev\vosaka\runtime\eventloop\task\Task** |  |





***

### handleGenerator

Generator handling with match expression

```php
private handleGenerator(\venndev\vosaka\runtime\eventloop\task\Task $task): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$task` | **\venndev\vosaka\runtime\eventloop\task\Task** |  |





***

### addDeferredTask

Deferred task addition with pooling

```php
private addDeferredTask(\venndev\vosaka\runtime\eventloop\task\Task $task, \venndev\vosaka\utils\Defer $defer): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$task` | **\venndev\vosaka\runtime\eventloop\task\Task** |  |
| `$defer` | **\venndev\vosaka\utils\Defer** |  |





***

### handleMemoryManagement

Memory management with reduced frequency

```php
private handleMemoryManagement(): void
```












***

### handleYielding

Smart yielding with adaptive behavior

```php
private handleYielding(): void
```












***

### resetCycleCounters



```php
private resetCycleCounters(): void
```












***

### shouldYieldControl



```php
private shouldYieldControl(): bool
```












***

### completeTask

Task completion with pooled arrays

```php
private completeTask(\venndev\vosaka\runtime\eventloop\task\Task $task, mixed $result = null): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$task` | **\venndev\vosaka\runtime\eventloop\task\Task** |  |
| `$result` | **mixed** |  |





***

### failTask



```php
private failTask(\venndev\vosaka\runtime\eventloop\task\Task $task, \Throwable $error): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$task` | **\venndev\vosaka\runtime\eventloop\task\Task** |  |
| `$error` | **\Throwable** |  |





***

### close



```php
public close(): void
```












***

### setMaxTasksPerCycle



```php
public setMaxTasksPerCycle(int $maxTasks): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$maxTasks` | **int** |  |





***

### setMaxQueueSize



```php
public setMaxQueueSize(int $maxSize): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$maxSize` | **int** |  |





***

### setMaxExecutionTime



```php
public setMaxExecutionTime(float $maxTime): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$maxTime` | **float** |  |





***

### setBackpressureEnabled



```php
public setBackpressureEnabled(bool $enabled): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$enabled` | **bool** |  |





***

### setBackpressureThreshold



```php
public setBackpressureThreshold(int $threshold): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$threshold` | **int** |  |





***

### setIterationLimit



```php
public setIterationLimit(int $limit): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$limit` | **int** |  |





***

### resetIterationLimit



```php
public resetIterationLimit(): void
```












***

### resetIteration



```php
public resetIteration(): void
```












***

### canContinueIteration



```php
public canContinueIteration(): bool
```












***

### isLimitedToIterations



```php
public isLimitedToIterations(): bool
```












***

### getStats

Enhanced statistics with performance metrics

```php
public getStats(): array
```












***

### enableHighPerformanceMode

Apply performance tuning for high-throughput scenarios

```php
public enableHighPerformanceMode(): void
```












***

### enableMemoryConservativeMode

Apply conservative tuning for memory-constrained environments

```php
public enableMemoryConservativeMode(): void
```












***

### hasReadyTasks



```php
private hasReadyTasks(): bool
```












***

### getQueueSize



```php
private getQueueSize(): int
```












***


***
> Automatically generated on 2025-06-28
