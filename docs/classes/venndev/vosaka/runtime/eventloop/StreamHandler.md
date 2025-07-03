***

# StreamHandler

This class is responsible for managing read/write streams and signal handling.



* Full name: `\venndev\vosaka\runtime\eventloop\StreamHandler`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### readStreams



```php
private array $readStreams
```






***

### readListeners



```php
private array $readListeners
```






***

### writeStreams



```php
private array $writeStreams
```






***

### writeListeners



```php
private array $writeListeners
```






***

### pcntl



```php
private bool $pcntl
```






***

### pcntlPoll



```php
private bool $pcntlPoll
```






***

### signals



```php
private array $signals
```






***

## Methods


### __construct



```php
public __construct(): mixed
```












***

### addReadStream

Add a read stream to the handler

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

Add a write stream to the handler

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

Remove a read stream from the handler

```php
public removeReadStream(mixed $stream): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$stream` | **mixed** |  |





***

### removeWriteStream

Remove a write stream from the handler

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

### handleSignal

Handle signal internally

```php
public handleSignal(int $signal): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$signal` | **int** |  |





***

### waitForStreamActivity

Wait for stream activity

```php
public waitForStreamActivity(?int $timeout): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$timeout` | **?int** |  |





***

### streamSelect

Stream select implementation with Windows compatibility

```php
private streamSelect(array& $read, array& $write, ?int $timeout): int|false
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$read` | **array** |  |
| `$write` | **array** |  |
| `$timeout` | **?int** |  |





***

### hasStreams

Check if handler has streams

```php
public hasStreams(): bool
```












***

### hasSignals

Check if handler has signals

```php
public hasSignals(): bool
```












***

### close

Close and clean up all streams and signals

```php
public close(): void
```












***

### getStats

Get statistics about streams and signals

```php
public getStats(): array
```












***


***
> Automatically generated on 2025-07-03
