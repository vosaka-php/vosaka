***

# StreamHandlerTrait





* Full name: `\venndev\vosaka\net\StreamHandlerTrait`



## Properties


### readRegistered



```php
protected bool $readRegistered
```






***

### writeRegistered



```php
protected bool $writeRegistered
```






***

### consecutiveReadErrors



```php
protected int $consecutiveReadErrors
```






***

### consecutiveWriteErrors



```php
protected int $consecutiveWriteErrors
```






***

### maxConsecutiveErrors



```php
protected int $maxConsecutiveErrors
```






***

## Methods


### performRead

Common read handler implementation with error recovery

```php
protected performRead(): void
```












***

### performWrite

Common write handler implementation with error recovery and backpressure handling

```php
protected performWrite(): void
```












***

### registerReadHandler

Safely register read handler with the event loop

```php
protected registerReadHandler(): void
```












***

### unregisterReadHandler

Safely unregister read handler from the event loop

```php
protected unregisterReadHandler(): void
```












***

### registerWriteHandler

Safely register write handler with the event loop

```php
protected registerWriteHandler(): void
```












***

### unregisterWriteHandler

Safely unregister write handler from the event loop

```php
protected unregisterWriteHandler(): void
```












***

### isSocketHealthy

Check if socket is in a healthy state

```php
protected isSocketHealthy(): bool
```












***

### closeWithError

Close socket with error logging

```php
protected closeWithError(string $reason): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$reason` | **string** |  |





***

### cleanupHandlers

Clean up all handlers and socket

```php
protected cleanupHandlers(): void
```












***

### getMaxReadCycles

Get maximum read cycles per event loop iteration

```php
protected getMaxReadCycles(): int
```












***

### getMaxBytesPerCycle

Get maximum bytes to read per cycle

```php
protected getMaxBytesPerCycle(): int
```












***

### getMaxWriteCycles

Get maximum write cycles per event loop iteration

```php
protected getMaxWriteCycles(): int
```












***

### getWriteChunkSize

Get write chunk size for backpressure handling

```php
protected getWriteChunkSize(): int
```












***

### initializeStream

Initialize stream with proper socket setup

```php
protected initializeStream(mixed $socket, array $options = []): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$socket` | **mixed** |  |
| `$options` | **array** |  |





***

### validateWrite

Validate write operation before executing

```php
protected validateWrite(string $data): bool
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$data` | **string** |  |





***

### shouldRegisterWriteHandler

Check if we need to register write handler for buffered data

```php
protected shouldRegisterWriteHandler(): bool
```












***

***
> Automatically generated on 2025-07-14

