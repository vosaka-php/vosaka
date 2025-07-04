***

# EventLoop

This class focuses on the main event loop operations and coordination.



* Full name: `\venndev\vosaka\runtime\eventloop\EventLoop`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### taskManager



```php
private \venndev\vosaka\runtime\eventloop\task\TaskManager $taskManager
```






***

### streamHandler



```php
private \venndev\vosaka\runtime\eventloop\StreamHandler $streamHandler
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
public getStreamHandler(): \venndev\vosaka\runtime\eventloop\StreamHandler
```












***

### getTaskManager



```php
public getTaskManager(): \venndev\vosaka\runtime\eventloop\task\TaskManager
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

Main run loop with stream support and batch processing

```php
public run(): void
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

### getStats



```php
public getStats(): array
```












***


***
> Automatically generated on 2025-07-04
