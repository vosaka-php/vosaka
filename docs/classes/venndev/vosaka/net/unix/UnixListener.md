***

# UnixListener





* Full name: `\venndev\vosaka\net\unix\UnixListener`
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

### path



```php
private string $path
```






***

## Methods


### __construct



```php
private __construct(string $path, array $options = []): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** |  |
| `$options` | **array** |  |





***

### bind

Binds a Unix domain socket listener to the specified path.

```php
public static bind(string $path, array $options = []): \venndev\vosaka\core\Result&lt;\venndev\vosaka\net\unix\UnixListener&gt;
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The path to bind the socket to. |
| `$options` | **array** | Optional socket options. |


**Return Value:**

A Result containing the UnixListener on success.



**Throws:**
<p>If the path is invalid or binding fails.</p>

- [`InvalidArgumentException`](../../../../InvalidArgumentException.md)



***

### bindSocket



```php
private bindSocket(): \venndev\vosaka\core\Result
```












***

### accept

Accepts a new connection on the Unix domain socket.

```php
public accept(float $timeout = 0.0): \venndev\vosaka\core\Result&lt;\venndev\vosaka\net\unix\UnixStream&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$timeout` | **float** | Optional timeout in seconds for accepting a connection. |


**Return Value:**

A Result containing the UnixStream on success.



**Throws:**
<p>If the listener is not bound or accept fails.</p>

- [`InvalidArgumentException`](../../../../InvalidArgumentException.md)



***

### localAddr

Returns the local address of the Unix domain socket.

```php
public localAddr(): string
```









**Return Value:**

The path to the Unix socket.




***

### getOptions

Returns the options used for the UnixListener.

```php
public getOptions(): array
```









**Return Value:**

The socket options.




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

Checks if the listener is currently listening for connections.

```php
public close(): bool
```









**Return Value:**

True if the listener is listening, false otherwise.




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
> Automatically generated on 2025-07-03
