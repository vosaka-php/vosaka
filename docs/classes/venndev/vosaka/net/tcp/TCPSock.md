***

# TCPSock





* Full name: `\venndev\vosaka\net\tcp\TCPSock`
* Parent class: [`\venndev\vosaka\net\SocketBase`](../SocketBase.md)
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### bound



```php
private bool $bound
```






***

### addr



```php
private string $addr
```






***

### port



```php
private int $port
```






***

### family



```php
private string $family
```






***

## Methods


### __construct



```php
private __construct(string $family = &quot;v4&quot;, array $options = []): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$family` | **string** |  |
| `$options` | **array** |  |





***

### newV4

Creates a new TCP socket instance.

```php
public static newV4(array $options = []): self
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$options` | **array** | Optional socket options. |





***

### newV6

Creates a new TCP socket instance for IPv6.

```php
public static newV6(array $options = []): self
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$options` | **array** | Optional socket options. |





***

### bind

Parses the address into host and port.

```php
public bind(string $addr): array&lt;string,int&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$addr` | **string** | The address in &quot;host:port&quot; format. |


**Return Value:**

An array containing the host and port.




***

### listen

Listens for incoming connections on the bound address.

```php
public listen(int $backlog = SOMAXCONN): \venndev\vosaka\core\Result&lt;\venndev\vosaka\net\tcp\TCPListener&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$backlog` | **int** | The maximum number of pending connections. |


**Return Value:**

A Result containing the TCPListener on success.



**Throws:**
<p>If the socket is not bound.</p>

- [`InvalidArgumentException`](../../../../InvalidArgumentException.md)



***

### connect

Connects to a TCP server at the specified address.

```php
public connect(string $addr): \venndev\vosaka\core\Result&lt;\venndev\vosaka\net\tcp\TCPStream&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$addr` | **string** | The address to connect to, in the format &quot;host:port&quot;. |


**Return Value:**

A Result containing the TCPStream on success.



**Throws:**
<p>If the address is invalid or connection fails.</p>

- [`InvalidArgumentException`](../../../../InvalidArgumentException.md)



***

### setReuseAddr

Sets the socket option to reuse the address.

```php
public setReuseAddr(bool $reuseAddr): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$reuseAddr` | **bool** | Whether to reuse the address. |





***

### setReusePort

Sets the socket option to reuse the port.

```php
public setReusePort(bool $reusePort): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$reusePort` | **bool** | Whether to reuse the port. |





***

### setKeepAlive

Sets the socket option to keep the connection alive.

```php
public setKeepAlive(bool $keepAlive): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$keepAlive` | **bool** | Whether to keep the connection alive. |





***

### setNoDelay

Sets the socket option to disable Nagle's algorithm.

```php
public setNoDelay(bool $noDelay): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$noDelay` | **bool** | Whether to disable Nagle&#039;s algorithm. |





***

### setSsl

Sets the socket option for SSL/TLS.

```php
public setSsl(bool $ssl, string|null $sslCert = null, string|null $sslKey = null): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$ssl` | **bool** | Whether to enable SSL/TLS. |
| `$sslCert` | **string&#124;null** | Path to the SSL certificate file. |
| `$sslKey` | **string&#124;null** | Path to the SSL key file. |





***

### getLocalAddr

Returns the address family of the socket.

```php
public getLocalAddr(): string
```









**Return Value:**

The address family ("v4" or "v6").




***


## Inherited methods


### createContext



```php
protected static createContext(array $options = []): resource
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$options` | **array** |  |





***

### applySocketOptions



```php
protected static applySocketOptions(mixed $socket, array $options): void
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$socket` | **mixed** |  |
| `$options` | **array** |  |





***

### validatePath



```php
protected static validatePath(string $path): void
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** |  |





***

### parseAddr



```php
protected static parseAddr(string $addr): array
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$addr` | **string** |  |





***

### addToEventLoop



```php
protected static addToEventLoop(mixed $socket): void
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$socket` | **mixed** |  |





***

### removeFromEventLoop



```php
protected static removeFromEventLoop(mixed $socket): void
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$socket` | **mixed** |  |





***

### normalizeOptions

Normalizes the provided socket options.

```php
protected static normalizeOptions(array|\venndev\vosaka\net\option\SocketOptions|null $options = null): array
```

If an instance of SocketOptions is provided, it converts it to an array.
If an array is provided, it merges it with the default options.
If no options are provided, it returns the default socket options.

* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$options` | **array&#124;\venndev\vosaka\net\option\SocketOptions&#124;null** |  |





***


***
> Automatically generated on 2025-07-16
