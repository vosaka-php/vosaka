***

# GracefulShutdown

Main graceful shutdown orchestrator



* Full name: `\venndev\vosaka\cleanup\GracefulShutdown`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### socketHandler



```php
private \venndev\vosaka\cleanup\SocketCleanupHandler $socketHandler
```






***

### pipeHandler



```php
private \venndev\vosaka\cleanup\PipeCleanupHandler $pipeHandler
```






***

### processHandler



```php
private \venndev\vosaka\cleanup\handler\ProcessCleanupHandler $processHandler
```






***

### childProcessHandler



```php
private \venndev\vosaka\cleanup\handler\ChildProcessHandler $childProcessHandler
```






***

### tempFileHandler



```php
private \venndev\vosaka\cleanup\handler\TempFileHandler $tempFileHandler
```






***

### callbackHandler



```php
private \venndev\vosaka\cleanup\handler\CallbackHandler $callbackHandler
```






***

### stateManager



```php
private \venndev\vosaka\cleanup\handler\StateManager $stateManager
```






***

### logger



```php
private \venndev\vosaka\cleanup\logger\FileLogger $logger
```






***

### isRegistered



```php
private bool $isRegistered
```






***

### isWindows



```php
private bool $isWindows
```






***

## Methods


### __construct



```php
public __construct(string $stateFile = &#039;/tmp/graceful_shutdown_state.json&#039;, string $logFile = &#039;/tmp/graceful_shutdown.log&#039;, bool $enableLogging = false): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$stateFile` | **string** |  |
| `$logFile` | **string** |  |
| `$enableLogging` | **bool** |  |





***

### setStateFile



```php
public setStateFile(string $stateFile): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$stateFile` | **string** |  |





***

### setLogFile



```php
public setLogFile(string $logFile): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$logFile` | **string** |  |





***

### setLogging



```php
public setLogging(bool $enableLogging): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$enableLogging` | **bool** |  |





***

### addSocket



```php
public addSocket(mixed $socket): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$socket` | **mixed** |  |





***

### addTempFile



```php
public addTempFile(string $filePath): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$filePath` | **string** |  |





***

### addChildProcess



```php
public addChildProcess(int $pid): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$pid` | **int** |  |





***

### addPipe



```php
public addPipe(mixed $pipe): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$pipe` | **mixed** |  |





***

### addPipes



```php
public addPipes(array $pipes): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$pipes` | **array** |  |





***

### addProcess



```php
public addProcess(mixed $process): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$process` | **mixed** |  |





***

### addProcOpen



```php
public addProcOpen(mixed $process, array $pipes = []): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$process` | **mixed** |  |
| `$pipes` | **array** |  |





***

### addCleanupCallback



```php
public addCleanupCallback(callable $callback): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$callback` | **callable** |  |





***

### removeSocket



```php
public removeSocket(mixed $socket): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$socket` | **mixed** |  |





***

### removePipe



```php
public removePipe(mixed $pipe): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$pipe` | **mixed** |  |





***

### removeProcess



```php
public removeProcess(mixed $process): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$process` | **mixed** |  |





***

### removeTempFile



```php
public removeTempFile(string $path): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** |  |





***

### removeChildProcessPid



```php
public removeChildProcessPid(string $pid): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$pid` | **string** |  |





***

### cleanup



```php
public cleanup(): void
```












***

### cleanupAll



```php
public cleanupAll(): void
```












***

### getResourceCounts



```php
public getResourceCounts(): array
```












***

### handleTermination



```php
public handleTermination(mixed $signal): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$signal` | **mixed** |  |





***

### handleWindowsCtrlC



```php
public handleWindowsCtrlC(mixed $event): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$event` | **mixed** |  |





***

### handleFatalError



```php
public handleFatalError(): void
```












***

### registerCleanupHandlers



```php
private registerCleanupHandlers(): void
```












***

### performCleanup



```php
private performCleanup(): void
```












***

### saveCurrentState



```php
private saveCurrentState(): void
```












***

### __destruct



```php
public __destruct(): mixed
```












***


***
> Automatically generated on 2025-07-02
