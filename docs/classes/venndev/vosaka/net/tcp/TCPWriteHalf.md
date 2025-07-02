***

# TCPWriteHalf

TCPWriteHalf represents the write-only half of a split TCP stream.

This class provides write-only access to a TCP socket, allowing for
separation of read and write operations on the same underlying socket.

* Full name: `\venndev\vosaka\net\tcp\TCPWriteHalf`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### isClosed



```php
private bool $isClosed
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
public __construct(mixed $socket, string $peerAddr = &quot;&quot;): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$socket` | **mixed** |  |
| `$peerAddr` | **string** |  |





***

### handleWrite

Handle outgoing data to the socket.

```php
public handleWrite(): void
```












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

Close the write half and cleanup resources.

```php
public close(): void
```












***


***
> Automatically generated on 2025-07-02
