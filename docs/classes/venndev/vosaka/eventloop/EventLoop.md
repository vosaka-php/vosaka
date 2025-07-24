***

# EventLoop





* Full name: `\venndev\vosaka\eventloop\EventLoop`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### taskManager



```php
private \venndev\vosaka\eventloop\task\TaskManager $taskManager
```






***

### streamHandler



```php
private \venndev\vosaka\eventloop\StreamHandler $streamHandler
```






***

### gracefulShutdown



```php
private ?\venndev\vosaka\cleanup\GracefulShutdown $gracefulShutdown
```






***

### isRunning



```php
private bool $isRunning
```






***

### maxTasksPerCycle



```php
private int $maxTasksPerCycle
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

### hasTasksCache



```php
private bool $hasTasksCache
```






***

### hasStreamsCache



```php
private bool $hasStreamsCache
```






***

### cacheInvalidationCounter



```php
private int $cacheInvalidationCounter
```






***

### streamCheckInterval



```php
private int $streamCheckInterval
```






***

### streamCheckCounter



```php
private int $streamCheckCounter
```






***

### batchSize



```php
private int $batchSize
```






***

### consecutiveEmptyCycles



```php
private int $consecutiveEmptyCycles
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

## Methods


### __construct



```php
public __construct(): mixed
```












***

### getGracefulShutdown



```php
public getGracefulShutdown(): \venndev\vosaka\cleanup\GracefulShutdown
```












***

### getStreamHandler



```php
public getStreamHandler(): \venndev\vosaka\eventloop\StreamHandler
```












***

### getTaskManager



```php
public getTaskManager(): \venndev\vosaka\eventloop\task\TaskManager
```












***

### addReadStream

Add a read stream to the event loop

```php
public addReadStream(mixed $stream, callable $listener): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$stream` | **mixed** |  |
| `$listener` | **callable** |  |





***

### addWriteStream

Add a write stream to the event loop

```php
public addWriteStream(mixed $stream, callable $listener): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$stream` | **mixed** |  |
| `$listener` | **callable** |  |





***

### removeReadStream

Remove a read stream from the event loop

```php
public removeReadStream(mixed $stream): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$stream` | **mixed** |  |





***

### removeWriteStream

Remove a write stream from the event loop

```php
public removeWriteStream(mixed $stream): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$stream` | **mixed** |  |





***

### addSignal

Add signal handler

```php
public addSignal(int $signal, callable $listener): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$signal` | **int** |  |
| `$listener` | **callable** |  |





***

### removeSignal

Remove signal handler

```php
public removeSignal(int $signal, callable $listener): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$signal` | **int** |  |
| `$listener` | **callable** |  |





***

### spawn

Spawn method - delegates to TaskManager

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

Optimized main run loop with batch processing

```php
public run(): void
```












***

### processBatchTasks

Process tasks in batches for improved performance

```php
private processBatchTasks(): void
```












***

### handleStreamActivity

Smart stream handling with reduced overhead

```php
private handleStreamActivity(): void
```












***

### calculateStreamTimeout

Calculate optimal stream timeout based on current workload

```php
private calculateStreamTimeout(): int
```












***

### handleYielding

Smart yielding with adaptive behavior

```php
private handleYielding(): void
```












***

### shouldYieldControl

Check if we should yield control

```php
private shouldYieldControl(): bool
```












***

### isTimeLimitExceeded

Check if time limit exceeded

```php
private isTimeLimitExceeded(): bool
```












***

### resetCycleCounters

Reset cycle counters

```php
private resetCycleCounters(): void
```












***

### hasWork

Cached check for work

```php
private hasWork(): bool
```












***

### hasTasksCached

Cached check for tasks

```php
private hasTasksCached(): bool
```












***

### hasStreams

Cached check for streams

```php
private hasStreams(): bool
```












***

### invalidateTaskCache

Invalidate task cache

```php
private invalidateTaskCache(): void
```












***

### invalidateStreamCache

Invalidate stream cache

```php
private invalidateStreamCache(): void
```












***

### stop



```php
public stop(): void
```












***

### close



```php
public close(): void
```












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

### isLimitedToIterations



```php
public isLimitedToIterations(): bool
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

### setMaxExecutionTime



```php
public setMaxExecutionTime(float $maxTime): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$maxTime` | **float** |  |





***

### setBatchSize



```php
public setBatchSize(int $size): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$size` | **int** |  |





***

### setStreamCheckInterval



```php
public setStreamCheckInterval(int $interval): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$interval` | **int** |  |





***

### enableHighPerformanceMode

Apply performance tuning for high-throughput scenarios

```php
public enableHighPerformanceMode(): void
```












***

### enableBalancedMode

Apply balanced tuning for mixed workloads

```php
public enableBalancedMode(): void
```












***

### enableStreamMode

Apply stream-optimized tuning

```php
public enableStreamMode(): void
```












***

### getStats



```php
public getStats(): array
```












***


***
> Automatically generated on 2025-07-24
