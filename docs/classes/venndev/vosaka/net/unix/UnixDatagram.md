***

# UnixDatagram

Unix datagram socket for connectionless communication.

This class provides asynchronous Unix datagram socket functionality for
connectionless communication over Unix domain sockets. It supports both
bound and unbound sockets, allowing for flexible client-server or
peer-to-peer communication patterns.

All operations are non-blocking and return Result objects that can be
awaited using VOsaka's async runtime. The class handles socket creation,
binding, and proper cleanup of socket files.

* Full name: `\venndev\vosaka\net\unix\UnixDatagram`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### socket



```php
private mixed $socket
```






***

### bound



```php
private bool $bound
```






***

### path



```php
private string $path
```






***

### options



```php
private array $options
```






***

## Methods


### __construct



```php
private __construct(array $options = []): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$options` | **array** |  |





***

### new

Create a new Unix datagram socket.

```php
public static new(array $options = []): \venndev\vosaka\net\unix\UnixDatagram
```

Creates a new Unix datagram socket instance that can be used for
connectionless communication. The socket can be bound to a path
or used unbound for client operations.

Available options:
- 'reuseaddr' (bool): Whether to reuse the address (default: true)

* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$options` | **array** | Socket configuration options |


**Return Value:**

A new UnixDatagram instance




***

### bind

Bind the socket to a Unix domain socket path.

```php
public bind(string $path): \venndev\vosaka\core\Result&lt;self&gt;
```

Binds the datagram socket to the specified path, creating the socket
file on the filesystem. The socket must be bound before it can receive
data. If the path already exists, it will be removed first.






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | Path to the Unix socket file |


**Return Value:**

The bound socket instance



**Throws:**
<p>If the path is invalid or binding fails</p>

- [`InvalidArgumentException`](../../../../InvalidArgumentException.md)



***

### sendTo

Send data to a specific Unix socket path.

```php
public sendTo(string $data, string $path): \venndev\vosaka\core\Result&lt;int&gt;
```

Sends data to the specified Unix domain socket path. The socket does
not need to be bound to send data, but the target path must exist
and have a socket listening on it.






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$data` | **string** | Data to send |
| `$path` | **string** | Path to the target Unix socket |


**Return Value:**

Number of bytes sent



**Throws:**
<p>If sending fails</p>

- [`InvalidArgumentException`](../../../../InvalidArgumentException.md)



***

### receiveFrom

Receive data from the Unix socket.

```php
public receiveFrom(int $maxLength = 65535): \venndev\vosaka\core\Result&lt;array{data: string, peerPath: string}&gt;
```

Receives data from the bound Unix datagram socket. The socket must be
bound before it can receive data. Returns both the data and the path
of the sender.






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$maxLength` | **int** | Maximum number of bytes to receive |


**Return Value:**

Received data and sender path



**Throws:**
<p>If the socket is not bound or receive fails</p>

- [`InvalidArgumentException`](../../../../InvalidArgumentException.md)



***

### setReuseAddr

Set socket reuse address option.

```php
public setReuseAddr(bool $reuseAddr): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$reuseAddr` | **bool** | Whether to reuse the address |


**Return Value:**

This instance for method chaining




***

### localPath

Get the local socket path.

```php
public localPath(): string
```









**Return Value:**

The local socket path, empty if not bound




***

### close

Close the datagram socket.

```php
public close(): void
```

Closes the socket and cleans up the socket file if it was bound.
This method is idempotent and can be called multiple times safely.










***

### isClosed

Check if the socket is closed.

```php
public isClosed(): bool
```









**Return Value:**

True if the socket is closed, false otherwise




***

### validatePath

Validate Unix domain socket path.

```php
private static validatePath(string $path): void
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | Path to validate |




**Throws:**
<p>If path is invalid</p>

- [`InvalidArgumentException`](../../../../InvalidArgumentException.md)



***

### createContext

Create stream context with options.

```php
private createContext(): resource
```









**Return Value:**

Stream context




***

### configureSocket

Configure socket with options.

```php
private configureSocket(): void
```












***


***
> Automatically generated on 2025-07-02
