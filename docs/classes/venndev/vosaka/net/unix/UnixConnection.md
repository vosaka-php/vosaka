***

# UnixConnection

Unix Socket Connection implementation



* Full name: `\venndev\vosaka\net\unix\UnixConnection`
* Parent class: [`\venndev\vosaka\net\AbstractConnection`](../AbstractConnection.md)
* This class implements:
[`\venndev\vosaka\net\contracts\StreamInterface`](../contracts/StreamInterface.md)




## Methods


### __construct



```php
public __construct(mixed $socket, \venndev\vosaka\net\unix\UnixAddress $localAddress, \venndev\vosaka\net\unix\UnixAddress $remoteAddress): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$socket` | **mixed** |  |
| `$localAddress` | **\venndev\vosaka\net\unix\UnixAddress** |  |
| `$remoteAddress` | **\venndev\vosaka\net\unix\UnixAddress** |  |





***

### getLocalAddress

Get local address

```php
public getLocalAddress(): \venndev\vosaka\net\contracts\AddressInterface
```












***

### getRemoteAddress

Get remote address

```php
public getRemoteAddress(): \venndev\vosaka\net\contracts\AddressInterface
```












***

### readLine

Read a line from the connection asynchronously.

```php
public readLine(): \venndev\vosaka\core\Result&lt;string&gt;
```












***

### doReadLine

Read a line from the connection.

```php
private doReadLine(): \Generator&lt;string&gt;
```

This method is a generator that yields the line read from the buffer.









**Throws:**

- [`NetworkException`](../exceptions/NetworkException.md)

- [`ConnectionException`](../exceptions/ConnectionException.md)



***

### readUntil

Read data until a specific delimiter is found.

```php
public readUntil(string $delimiter): \venndev\vosaka\core\Result&lt;string&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$delimiter` | **string** | Delimiter to read until |





***

### doReadUntil

Read data until a specific delimiter is found.

```php
private doReadUntil(string $delimiter): \Generator&lt;string&gt;
```

This method is a generator that yields the data read until the delimiter.






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$delimiter` | **string** | Delimiter to read until |




**Throws:**

- [`NetworkException`](../exceptions/NetworkException.md)

- [`ConnectionException`](../exceptions/ConnectionException.md)



***

### readExact

Read an exact number of bytes from the connection.

```php
public readExact(int $bytes): \venndev\vosaka\core\Result&lt;string&gt;
```

This method returns a Result that resolves to the data read.






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$bytes` | **int** | Number of bytes to read |





***

### doReadExact

Read an exact number of bytes from the connection.

```php
private doReadExact(int $bytes): \Generator&lt;string&gt;
```

This method is a generator that yields the data read.






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$bytes` | **int** | Number of bytes to read |




**Throws:**

- [`NetworkException`](../exceptions/NetworkException.md)

- [`ConnectionException`](../exceptions/ConnectionException.md)



***

### writeAll

Write data to the connection asynchronously.

```php
public writeAll(string $data): \venndev\vosaka\core\Result&lt;int&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$data` | **string** | Data to write |


**Return Value:**

Number of bytes written




***

### doWriteAll

Write data to the connection.

```php
private doWriteAll(string $data): \Generator&lt;int&gt;
```

This method is a generator that yields the number of bytes written.






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$data` | **string** | Data to write |




**Throws:**

- [`NetworkException`](../exceptions/NetworkException.md)

- [`ConnectionException`](../exceptions/ConnectionException.md)



***

### flush

Flush the write buffer asynchronously.

```php
public flush(): \venndev\vosaka\core\Result
```












***

### doFlush

Flush the write buffer.

```php
private doFlush(): \Generator
```

This method is a generator that yields until the buffer is empty.









**Throws:**

- [`NetworkException`](../exceptions/NetworkException.md)



***

### readable

Check if the connection is readable.

```php
public readable(): bool
```












***

### writable

Check if the connection is writable.

```php
public writable(): bool
```












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
