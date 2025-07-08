***

# TCPListener





* Full name: `\venndev\vosaka\net\tcp\TCPListener`
* Parent class: [`\venndev\vosaka\net\SocketBase`](../SocketBase.md)
* This class is marked as **final** and can't be subclassed
* This class implements:
[`\venndev\vosaka\net\ListenerInterface`](../ListenerInterface.md)
* This class is a **Final class**



## Properties


### isListening



```php
private bool $isListening
```






***

### host



```php
private string $host
```






***

### port



```php
private int $port
```






***

## Methods


### __construct



```php
private __construct(string $host, int $port, array $options = []): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$host` | **string** |  |
| `$port` | **int** |  |
| `$options` | **array** |  |





***

### bind

Binds a TCP listener to the specified address.

```php
public static bind(string $addr, array $options = []): \venndev\vosaka\core\Result&lt;\venndev\vosaka\net\tcp\TCPListener&gt;
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$addr` | **string** | The address to bind to, in the format &quot;host:port&quot;. |
| `$options` | **array** | Optional socket options. |


**Return Value:**

A Result containing the TCPListener on success.



**Throws:**
<p>If the address is invalid or binding fails.</p>

- [`InvalidArgumentException`](../../../../InvalidArgumentException.md)



***

### bindSocket



```php
private bindSocket(): \venndev\vosaka\core\Result
```












***

### accept

Accepts a new incoming connection.

```php
public accept(float $timeout = 0.0): \venndev\vosaka\core\Result&lt;\venndev\vosaka\net\tcp\TCPStream|null&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$timeout` | **float** | Optional timeout in seconds for accepting connections. |


**Return Value:**

A Result containing the TCPStream on success, or null if no connection is available.



**Throws:**
<p>If the listener is not bound.</p>

- [`InvalidArgumentException`](../../../../InvalidArgumentException.md)



***

### localAddr

Returns the local address of the listener.

```php
public localAddr(): string
```









**Return Value:**

The local address in the format "host:port".




***

### getOptions

Returns the options used for this listener.

```php
public getOptions(): array
```









**Return Value:**

The socket options.




***

### isReusePortEnabled

Checks if the listener is currently listening for connections.

```php
public isReusePortEnabled(): bool
```









**Return Value:**

True if the listener is listening, false otherwise.




***

### getSocket

Returns the underlying socket resource.

```php
public getSocket(): resource|null
```









**Return Value:**

The socket resource, or null if not bound.




***

### close

Closes the listener and releases the socket resource.

```php
public close(): void
```












***

### isClosed

Checks if the listener is closed.

```php
public isClosed(): bool
```









**Return Value:**

True if the listener is closed, false otherwise.




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
> Automatically generated on 2025-07-08
