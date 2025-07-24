***

# AbstractConnection

Base implementation for connections



* Full name: `\venndev\vosaka\net\AbstractConnection`
* This class implements:
[`\venndev\vosaka\net\contracts\ConnectionInterface`](./contracts/ConnectionInterface.md), [`\venndev\vosaka\net\contracts\SocketInterface`](./contracts/SocketInterface.md)
* This class is an **Abstract class**



## Properties


### socket



```php
protected $socket
```






***

### closed



```php
protected bool $closed
```






***

### readBuffer



```php
protected \venndev\vosaka\net\StreamBuffer $readBuffer
```






***

### writeBuffer



```php
protected \venndev\vosaka\net\StreamBuffer $writeBuffer
```






***

### eventLoop



```php
protected \venndev\vosaka\net\EventLoopIntegration $eventLoop
```






***

### localAddress



```php
protected ?\venndev\vosaka\net\contracts\AddressInterface $localAddress
```






***

### remoteAddress



```php
protected ?\venndev\vosaka\net\contracts\AddressInterface $remoteAddress
```






***

### readTimeout



```php
protected float $readTimeout
```






***

### writeTimeout



```php
protected float $writeTimeout
```






***

## Methods


### __construct



```php
public __construct(mixed $socket): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$socket` | **mixed** |  |





***

### setupEventHandlers



```php
protected setupEventHandlers(): void
```












***

### handleRead

Handle readable event

```php
public handleRead(mixed $socket): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$socket` | **mixed** |  |





***

### handleWrite

Handle writable event

```php
public handleWrite(mixed $socket): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$socket` | **mixed** |  |





***

### read

Read data from the connection

```php
public read(int $length = -1): \venndev\vosaka\core\Result&lt;string&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$length` | **int** | Number of bytes to read, -1 for all available |





***

### doRead

Read data from the connection

```php
private doRead(int $length): \Generator&lt;string&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$length` | **int** | Number of bytes to read, -1 for all available |




**Throws:**

- [`NetworkException`](./exceptions/NetworkException.md)



***

### write

Write data to the connection

```php
public write(string $data): \venndev\vosaka\core\Result&lt;int&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$data` | **string** | Data to write |





***

### doWrite

Write data to the connection

```php
private doWrite(string $data): \Generator&lt;int&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$data` | **string** | Data to write |




**Throws:**

- [`NetworkException`](./exceptions/NetworkException.md)



***

### close

Close the connection

```php
public close(): void
```












***

### isClosed

Check if the connection is closed

```php
public isClosed(): bool
```












***

### setReadTimeout

Set the local address for this connection

```php
public setReadTimeout(float $seconds): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$seconds` | **float** |  |





***

### setWriteTimeout

Set the read timeout for this connection

```php
public setWriteTimeout(float $seconds): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$seconds` | **float** |  |





***

### getResource

Get the underlying socket resource

```php
public getResource(): resource
```












***

### setOption

Set a socket option

```php
public setOption(int $level, int $option, mixed $value): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$level` | **int** | The level at which the option resides |
| `$option` | **int** | The option to set |
| `$value` | **mixed** | The value to set for the option |





***

### getOption

Get a socket option

```php
public getOption(int $level, int $option): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$level` | **int** | The level at which the option resides |
| `$option` | **int** | The option to retrieve |


**Return Value:**

The value of the option, or null if not available




***

### setBlocking

Set the blocking mode for the socket

```php
public setBlocking(bool $blocking): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$blocking` | **bool** |  |





***

### shutdown

Terminate the connection gracefully

```php
public shutdown(int $how = 2): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$how` | **int** | 0=read, 1=write, 2=both |





***


***
> Automatically generated on 2025-07-24
