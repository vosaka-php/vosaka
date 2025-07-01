***

# TCPReadHalf

TCPReadHalf represents the read-only half of a split TCP stream.

This class provides read-only access to a TCP socket, allowing for
separation of read and write operations on the same underlying socket.

* Full name: `\venndev\vosaka\net\tcp\TCPReadHalf`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### isClosed



```php
private bool $isClosed
```






***

### readBuffer



```php
private string $readBuffer
```






***

### bufferSize



```php
private int $bufferSize
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
public __construct(mixed $socket, string $peerAddr = &quot;&quot;): mixed
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

### read

Read data from the stream.

```php
public read(int|null $maxBytes = null): \venndev\vosaka\core\Result&lt;string&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$maxBytes` | **int&#124;null** | Maximum number of bytes to read |


**Return Value:**

Data read from the stream




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

Data read from the stream



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

Data read from the stream (excluding delimiter)



**Throws:**
<p>If stream is closed before delimiter found</p>

- [`InvalidArgumentException`](../../../../InvalidArgumentException.md)



***

### readLine

Read a line from the stream.

```php
public readLine(): \venndev\vosaka\core\Result&lt;string&gt;
```









**Return Value:**

Line read from the stream (excluding newline)




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

Close the read half and cleanup resources.

```php
public close(): void
```












***


***
> Automatically generated on 2025-07-01
