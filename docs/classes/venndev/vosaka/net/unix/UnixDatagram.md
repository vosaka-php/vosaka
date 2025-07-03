***

# UnixDatagram





* Full name: `\venndev\vosaka\net\unix\UnixDatagram`
* Parent class: [`\venndev\vosaka\net\SocketBase`](../SocketBase.md)
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


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

## Methods


### bind

Creates a new Unix datagram socket instance.

```php
public static bind(string $path, array|\venndev\vosaka\net\option\SocketOptions $options = []): \venndev\vosaka\core\Result
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** |  |
| `$options` | **array&#124;\venndev\vosaka\net\option\SocketOptions** | Optional socket options. |





***

### sendTo

Sends data to a Unix datagram socket.

```php
public sendTo(string $data, string $path, array|\venndev\vosaka\net\option\SocketOptions $options = []): \venndev\vosaka\core\Result&lt;int&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$data` | **string** | The data to send. |
| `$path` | **string** | The path to the Unix socket. |
| `$options` | **array&#124;\venndev\vosaka\net\option\SocketOptions** | Optional socket options. |


**Return Value:**

A Result containing the number of bytes sent on success.



**Throws:**
<p>If the path is invalid or sending fails.</p>

- [`InvalidArgumentException`](../../../../InvalidArgumentException.md)



***

### receiveFrom

Receives data from a Unix datagram socket.

```php
public receiveFrom(int $maxLength = 65535): \venndev\vosaka\core\Result&lt;array{data: string, peerPath: string}&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$maxLength` | **int** | The maximum length of data to receive. |


**Return Value:**

A Result containing the received data and peer path.



**Throws:**
<p>If the socket is not bound or receiving fails.</p>

- [`InvalidArgumentException`](../../../../InvalidArgumentException.md)



***

### setReuseAddr

Sets the reuse address option for the socket.

```php
public setReuseAddr(bool $reuseAddr): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$reuseAddr` | **bool** | Whether to allow reusing the address. |


**Return Value:**

The current instance for method chaining.




***

### localPath

Validates the Unix socket path.

```php
public localPath(): string
```











**Throws:**
<p>If the path is invalid.</p>

- [`InvalidArgumentException`](../../../../InvalidArgumentException.md)



***

### close

Closes the Unix datagram socket and cleans up resources.

```php
public close(): void
```











**Throws:**
<p>If the socket is already closed.</p>

- [`InvalidArgumentException`](../../../../InvalidArgumentException.md)



***

### isClosed

Checks if the socket is closed.

```php
public isClosed(): bool
```









**Return Value:**

True if the socket is closed, false otherwise.




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
