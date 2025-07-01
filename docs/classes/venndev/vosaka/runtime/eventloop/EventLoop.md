***

# EventLoop

Enhanced EventLoop class with task execution and core functionality.

This class focuses on task execution and core event loop operations.

* Full name: `\venndev\vosaka\runtime\eventloop\EventLoop`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### taskPool



```php
private \venndev\vosaka\runtime\eventloop\task\TaskPool $taskPool
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

### batchSize



```php
private int $batchSize
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

### streamHandler



```php
private \venndev\vosaka\runtime\eventloop\StreamHandler $streamHandler
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

### getStreamHandler



```php
public getStreamHandler(): \venndev\vosaka\runtime\eventloop\StreamHandler
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

### run

Main run loop with stream support and batch processing

```php
public run(): void
```












***

### processRunningTasks

Process running tasks

```php
private processRunningTasks(): void
```












***

### calculateSelectTimeout

Calculate timeout for stream_select

```php
private calculateSelectTimeout(): ?int
```












***

### shouldStop

Check if event loop should stop

```php
private shouldStop(): bool
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

### setBatchSize



```php
public setBatchSize(int $size): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$size` | **int** |  |





***

### getStats



```php
public getStats(): array
```












***


***
> Automatically generated on 2025-07-01
