***

# TCPConnection

TCP Connection implementation



* Full name: `\venndev\vosaka\net\tcp\TCPConnection`
* Parent class: [`\venndev\vosaka\net\AbstractConnection`](../AbstractConnection.md)
* This class implements:
[`\venndev\vosaka\net\contracts\StreamInterface`](../contracts/StreamInterface.md)




## Methods


### __construct



```php
public __construct(mixed $socket, \venndev\vosaka\net\contracts\AddressInterface $localAddress, \venndev\vosaka\net\contracts\AddressInterface $remoteAddress): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$socket` | **mixed** |  |
| `$localAddress` | **\venndev\vosaka\net\contracts\AddressInterface** |  |
| `$remoteAddress` | **\venndev\vosaka\net\contracts\AddressInterface** |  |





***

### getLocalAddress

Get the local address of the connection

```php
public getLocalAddress(): \venndev\vosaka\net\contracts\AddressInterface
```












***

### getRemoteAddress

Get the remote address of the connection

```php
public getRemoteAddress(): \venndev\vosaka\net\contracts\AddressInterface
```












***

### readLine

Read a line from the stream

```php
public readLine(): \venndev\vosaka\core\Result&lt;string&gt;
```












***

### doReadLine

Internal method to read a line from the stream

```php
private doReadLine(): \Generator&lt;string&gt;
```











**Throws:**

- [`NetworkException`](../exceptions/NetworkException.md)

- [`ConnectionException`](../exceptions/ConnectionException.md)



***

### readUntil

Read data until a specific delimiter

```php
public readUntil(string $delimiter): \venndev\vosaka\core\Result&lt;string&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$delimiter` | **string** | Delimiter to read until |





***

### doReadUntil

Internal method to read data until a specific delimiter

```php
private doReadUntil(string $delimiter): \Generator&lt;string&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$delimiter` | **string** | Delimiter to read until |




**Throws:**

- [`NetworkException`](../exceptions/NetworkException.md)

- [`ConnectionException`](../exceptions/ConnectionException.md)



***

### readExact

Read an exact number of bytes from the stream

```php
public readExact(int $bytes): \venndev\vosaka\core\Result&lt;string&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$bytes` | **int** | Number of bytes to read |





***

### doReadExact

Internal method to read an exact number of bytes from the stream

```php
private doReadExact(int $bytes): \Generator&lt;string&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$bytes` | **int** | Number of bytes to read |




**Throws:**

- [`NetworkException`](../exceptions/NetworkException.md)

- [`ConnectionException`](../exceptions/ConnectionException.md)



***

### writeAll

Write data to the stream

```php
public writeAll(string $data): \venndev\vosaka\core\Result&lt;int&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$data` | **string** | Data to write |





***

### doWriteAll

Internal method to write all data to the stream

```php
private doWriteAll(string $data): \Generator&lt;int&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$data` | **string** | Data to write |




**Throws:**

- [`NetworkException`](../exceptions/NetworkException.md)

- [`ConnectionException`](../exceptions/ConnectionException.md)



***

### flush

Flush the write buffer

```php
public flush(): \venndev\vosaka\core\Result
```












***

### doFlush

Internal method to flush the write buffer

```php
private doFlush(): \Generator
```











**Throws:**

- [`NetworkException`](../exceptions/NetworkException.md)



***

### readable

Check if the connection is readable

```php
public readable(): bool
```












***

### writable

Check if the connection is writable

```php
public writable(): bool
```












***

### setKeepAlive

Enable TCP keepalive

```php
public setKeepAlive(bool $enable, int $idle = 7200, int $interval = 75, int $count = 9): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$enable` | **bool** |  |
| `$idle` | **int** |  |
| `$interval` | **int** |  |
| `$count` | **int** |  |





***

### setNoDelay

Enable/disable Nagle's algorithm

```php
public setNoDelay(bool $noDelay): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$noDelay` | **bool** |  |





***


## Inherited methods


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
