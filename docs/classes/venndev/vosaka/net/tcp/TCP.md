***

# TCP

TCP class for creating asynchronous TCP connections.

This class provides static methods for establishing TCP connections in an
asynchronous manner that works with the VOsaka event loop. It supports both
regular TCP connections and SSL/TLS encrypted connections with configurable
options.

All connection operations are non-blocking and return Result objects that
can be awaited using VOsaka's async runtime. The class handles connection
establishment, timeout management, and proper socket configuration for
async operations.

* Full name: `\venndev\vosaka\net\tcp\TCP`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**




## Methods


### connect

Connect to a remote TCP address asynchronously.

```php
public static connect(string $addr, array $options = []): \venndev\vosaka\core\Result&lt;\venndev\vosaka\net\tcp\TCPStream&gt;
```

Establishes a TCP connection to the specified remote address. The connection
is created asynchronously and the socket is configured for non-blocking
operation to work with the VOsaka event loop. Supports both plain TCP and
SSL/TLS encrypted connections.

The address should be in the format 'host:port' where host can be an IP
address or hostname, and port is the numeric port number.

Available options:
- 'ssl' (bool): Whether to use SSL/TLS encryption (default: false)
- 'timeout' (int): Connection timeout in seconds (default: 30)

* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$addr` | **string** | Address in the format &#039;host:port&#039; |
| `$options` | **array** | Additional connection options |


**Return Value:**

A Result containing the TCPStream on success



**Throws:**
<p>If the address format is invalid or connection fails</p>

- [`InvalidArgumentException`](../../../../InvalidArgumentException.md)



***


***
> Automatically generated on 2025-06-28
