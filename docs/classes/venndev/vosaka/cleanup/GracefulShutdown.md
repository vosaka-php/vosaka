***

# GracefulShutdown





* Full name: `\venndev\vosaka\cleanup\GracefulShutdown`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### sockets



```php
private array $sockets
```






***

### tempFiles



```php
private array $tempFiles
```






***

### childPids



```php
private array $childPids
```






***

### pipes



```php
private array $pipes
```






***

### processes



```php
private array $processes
```






***

### cleanupCallbacks



```php
private array $cleanupCallbacks
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

### enableLogging



```php
private bool $enableLogging
```






***

### stateFile



```php
private string $stateFile
```






***

### logFile



```php
private string $logFile
```






***

## Methods


### __construct



```php
public __construct(string $stateFile = &quot;/tmp/graceful_shutdown_state.json&quot;, string $logFile = &quot;/tmp/graceful_shutdown.log&quot;, bool $enableLogging = false): mixed
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

### registerCleanupHandlers



```php
private registerCleanupHandlers(): mixed
```












***

### cleanupPreviousState



```php
private cleanupPreviousState(): mixed
```












***

### saveState



```php
private saveState(): mixed
```












***

### log



```php
private log(string $message): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$message` | **string** |  |





***

### addSocket



```php
public addSocket(mixed $socket): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$socket` | **mixed** |  |





***

### addTempFile



```php
public addTempFile(string $filePath): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$filePath` | **string** |  |





***

### addChildProcess



```php
public addChildProcess(int $pid): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$pid` | **int** |  |





***

### addPipe



```php
public addPipe(mixed $pipe): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$pipe` | **mixed** |  |





***

### addPipes



```php
public addPipes(array $pipes): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$pipes` | **array** |  |





***

### addProcess



```php
public addProcess(mixed $process): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$process` | **mixed** |  |





***

### addProcOpen



```php
public addProcOpen(mixed $process, array $pipes = []): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$process` | **mixed** |  |
| `$pipes` | **array** |  |





***

### addCleanupCallback



```php
public addCleanupCallback(callable $callback): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$callback` | **callable** |  |





***

### pruneInvalidSockets



```php
private pruneInvalidSockets(): mixed
```












***

### pruneInvalidPipes



```php
private pruneInvalidPipes(): mixed
```












***

### pruneInvalidProcesses



```php
private pruneInvalidProcesses(): mixed
```












***

### handleTermination



```php
public handleTermination(mixed $signal): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$signal` | **mixed** |  |





***

### handleWindowsCtrlC



```php
public handleWindowsCtrlC(mixed $event): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$event` | **mixed** |  |





***

### handleFatalError



```php
public handleFatalError(): mixed
```












***

### performCleanup



```php
private performCleanup(bool $justInvalid = false): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$justInvalid` | **bool** |  |





***

### cleanup



```php
public cleanup(): mixed
```












***

### cleanupAll



```php
public cleanupAll(): mixed
```












***

### setLogging



```php
public setLogging(bool $enableLogging): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$enableLogging` | **bool** |  |





***

### __destruct



```php
public __destruct(): mixed
```












***


***
> Automatically generated on 2025-06-26
