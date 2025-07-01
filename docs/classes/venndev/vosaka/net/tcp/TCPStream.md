***

# TCPStream





* Full name: `\venndev\vosaka\net\tcp\TCPStream`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### isClosed



```php
private bool $isClosed
```






***

### bufferSize



```php
private int $bufferSize
```






***

### readQueue



```php
private \SplQueue $readQueue
```






***

### writeQueue



```php
private \SplQueue $writeQueue
```






***

### readBuffer



```php
private string $readBuffer
```






***

### isReading



```php
private bool $isReading
```






***

### isWriting



```php
private bool $isWriting
```






***

### readCallbacks



```php
private array $readCallbacks
```






***

### writeCallbacks



```php
private array $writeCallbacks
```






***

### socket



```php
private mixed $socket
```






***

### peerAddr



```php
private string $peerAddr
```






***

## Methods


### __construct



```php
public __construct(mixed $socket, string $peerAddr): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$socket` | **mixed** |  |
| `$peerAddr` | **string** |  |





***

### handleRead

Event-driven read handler

```php
public handleRead(): void
```












***

### handleWrite

Event-driven write handler

```php
public handleWrite(): void
```












***

### processReadQueue

Process pending read operations

```php
private processReadQueue(): void
```












***

### processRead



```php
private processRead(array $readOp): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$readOp` | **array** |  |





***

### processReadExact



```php
private processReadExact(array $readOp): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$readOp` | **array** |  |





***

### processReadUntil



```php
private processReadUntil(array $readOp): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$readOp` | **array** |  |





***

### read

Non-blocking read with event-driven approach

```php
public read(int|null $maxBytes = null): \venndev\vosaka\core\Result
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$maxBytes` | **int&#124;null** |  |





***

### readExact

Read exact number of bytes

```php
public readExact(int $bytes): \venndev\vosaka\core\Result
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$bytes` | **int** |  |





***

### readUntil

Read until delimiter

```php
public readUntil(string $delimiter): \venndev\vosaka\core\Result
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$delimiter` | **string** |  |





***

### queueReadOperation

Queue read operation and wait for completion

```php
private queueReadOperation(string $type, array $params): \Generator
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$type` | **string** |  |
| `$params` | **array** |  |





***

### write

Event-driven write

```php
public write(string $data): \venndev\vosaka\core\Result
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$data` | **string** |  |





***

### handleError

Handle connection errors

```php
private handleError(string $error): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$error` | **string** |  |





***

### handleConnectionClosed

Handle connection closed

```php
private handleConnectionClosed(): void
```












***

### readLine



```php
public readLine(): \venndev\vosaka\core\Result
```












***

### writeAll



```php
public writeAll(string $data): \venndev\vosaka\core\Result
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$data` | **string** |  |





***

### flush



```php
public flush(): \venndev\vosaka\core\Result
```












***

### peerAddr



```php
public peerAddr(): string
```












***

### close



```php
public close(): void
```












***

### isClosed



```php
public isClosed(): bool
```












***

### split



```php
public split(): array
```












***


***
> Automatically generated on 2025-07-01
