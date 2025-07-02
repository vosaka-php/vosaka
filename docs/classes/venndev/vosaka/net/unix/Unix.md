***

# Unix

Unix class for creating asynchronous Unix domain socket connections.

This class provides static methods for establishing Unix domain socket connections
in an asynchronous manner that works with the VOsaka event loop. It supports both
stream and datagram Unix domain sockets with configurable options.

All connection operations are non-blocking and return Result objects that
can be awaited using VOsaka's async runtime. The class handles connection
establishment, timeout management, and proper socket configuration for
async operations.

* Full name: `\venndev\vosaka\net\unix\Unix`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**




## Methods


### connect

Connect to a Unix domain socket asynchronously.

```php
public static connect(string $path, array $options = []): \venndev\vosaka\core\Result&lt;\venndev\vosaka\net\unix\UnixStream&gt;
```

Establishes a connection to the specified Unix domain socket path. The connection
is created asynchronously and the socket is configured for non-blocking
operation to work with the VOsaka event loop.

The path should be a valid Unix domain socket path on the filesystem.
The path length is limited to 108 characters (typical Unix socket path limit).

Available options:
- 'timeout' (int): Connection timeout in seconds (default: 30)
- 'reuseaddr' (bool): Whether to reuse the address (default: true)

* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | Path to the Unix domain socket |
| `$options` | **array** | Additional connection options |


**Return Value:**

A Result containing the UnixStream on success



**Throws:**
<p>If the path is invalid or connection fails</p>

- [`InvalidArgumentException`](../../../../InvalidArgumentException.md)



***

### datagram

Create a Unix datagram socket.

```php
public static datagram(array $options = []): \venndev\vosaka\net\unix\UnixDatagram
```

Creates a new Unix datagram socket that can be used for connectionless
communication over Unix domain sockets. The socket must be bound to a
path before it can be used for sending or receiving data.

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
private static createContext(array $options = []): resource
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$options` | **array** | Context options |


**Return Value:**

Stream context




***


***
> Automatically generated on 2025-07-02
