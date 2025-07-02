***

# TCPStream

TCPStream provides asynchronous TCP stream operations.

This class handles bidirectional TCP communication with non-blocking I/O,
buffering for optimal performance, and integration with the VOsaka event loop.
It supports reading, writing, and proper resource cleanup.

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

### readBuffer



```php
private string $readBuffer
```






***

### writeBuffer



```php
private string $writeBuffer
```






***

### writeRegistered



```php
private bool $writeRegistered
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

Handle incoming data from the socket.

```php
public handleRead(): void
```












***

### handleWrite

Handle outgoing data to the socket.

```php
public handleWrite(): void
```












***

### read

Read data from the stream.

```php
public read(int|null $maxBytes = null): \venndev\vosaka\core\Result&lt;string&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$maxBytes` | **int&#124;null** | Maximum bytes to read (null for all available) |


**Return Value:**

The read data




***

### readExact

Read exact number of bytes from the stream.

```php
public readExact(int $bytes): \venndev\vosaka\core\Result&lt;string&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$bytes` | **int** | Number of bytes to read |


**Return Value:**

The read data



**Throws:**
<p>If stream is closed before reading complete</p>

- [`InvalidArgumentException`](../../../../InvalidArgumentException.md)



***

### readUntil

Read until a delimiter is found.

```php
public readUntil(string $delimiter): \venndev\vosaka\core\Result&lt;string&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$delimiter` | **string** | The delimiter to read until |


**Return Value:**

The read data (excluding delimiter)



**Throws:**
<p>If stream is closed before delimiter found</p>

- [`InvalidArgumentException`](../../../../InvalidArgumentException.md)



***

### readLine

Read a line from the stream (until newline).

```php
public readLine(): \venndev\vosaka\core\Result&lt;string&gt;
```









**Return Value:**

The read line (excluding newline)




***

### write

Write data to the stream.

```php
public write(string $data): \venndev\vosaka\core\Result&lt;int&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$data` | **string** | Data to write |


**Return Value:**

Number of bytes written



**Throws:**
<p>If stream is closed or write fails</p>

- [`InvalidArgumentException`](../../../../InvalidArgumentException.md)



***

### writeAll

Write all data to the stream (alias for write).

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

### flush

Flush the stream buffer.

```php
public flush(): \venndev\vosaka\core\Result&lt;void&gt;
```












***

### peerAddr

Get the peer address.

```php
public peerAddr(): string
```









**Return Value:**

The peer address




***

### isClosed

Check if the stream is closed.

```php
public isClosed(): bool
```









**Return Value:**

True if closed




***

### close

Close the stream and cleanup resources.

```php
public close(): void
```












***

### split

Split the stream into separate read and write halves.

```php
public split(): array{: \venndev\vosaka\net\tcp\TCPReadHalf, : \venndev\vosaka\net\tcp\TCPWriteHalf}
```









**Return Value:**

Array containing read and write halves




***


***
> Automatically generated on 2025-07-02
