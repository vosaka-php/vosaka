***

# UnixStream





* Full name: `\venndev\vosaka\net\unix\UnixStream`
* Parent class: [`\venndev\vosaka\net\StreamBase`](../StreamBase.md)
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### path



```php
private string $path
```






***

## Methods


### __construct



```php
public __construct(mixed $socket, string $path, array $options = []): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$socket` | **mixed** |  |
| `$path` | **string** |  |
| `$options` | **array** |  |





***

### handleRead

Handles reading data from the Unix socket.

```php
public handleRead(): void
```

This method is called by the event loop when the socket is ready for reading.










***

### handleWrite

Handles write operations for the Unix stream.

```php
public handleWrite(): void
```

This method is called by the event loop when the socket is ready for writing.










***

### peerAddr

Returns the peer address of the Unix socket.

```php
public peerAddr(): string
```

This is typically the path of the Unix socket file.







**Return Value:**

The peer address.




***

### localPath

Returns the local path of the Unix socket.

```php
public localPath(): string
```

This is typically the path of the Unix socket file.







**Return Value:**

The local path of the Unix socket.




***

### getOptions

Returns the options set for the Unix stream.

```php
public getOptions(): array
```









**Return Value:**

The options array.




***

### setBufferSize

Sets the buffer size for reading and writing operations.

```php
public setBufferSize(int $size): \venndev\vosaka\net\unix\UnixStream
```

The buffer size must be greater than 0.






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$size` | **int** | The buffer size in bytes. |


**Return Value:**

The current instance for method chaining.



**Throws:**
<p>If the size is not greater than 0.</p>

- [`InvalidArgumentException`](../../../../InvalidArgumentException.md)



***

### setReadTimeout

Sets the read timeout for the Unix stream.

```php
public setReadTimeout(int $seconds): \venndev\vosaka\net\unix\UnixStream
```

The timeout must be greater than 0 seconds.






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$seconds` | **int** | The read timeout in seconds. |


**Return Value:**

The current instance for method chaining.



**Throws:**
<p>If the timeout is not greater than 0.</p>

- [`InvalidArgumentException`](../../../../InvalidArgumentException.md)



***

### setWriteTimeout

Sets the write timeout for the Unix stream.

```php
public setWriteTimeout(int $seconds): \venndev\vosaka\net\unix\UnixStream
```

The timeout must be greater than 0 seconds.






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$seconds` | **int** | The write timeout in seconds. |


**Return Value:**

The current instance for method chaining.



**Throws:**
<p>If the timeout is not greater than 0.</p>

- [`InvalidArgumentException`](../../../../InvalidArgumentException.md)



***

### split

Splits the Unix stream into read and write halves.

```php
public split(): (\venndev\vosaka\net\unix\UnixReadHalf|\venndev\vosaka\net\unix\UnixWriteHalf)[]
```

This allows for separate reading and writing operations on the same stream.







**Return Value:**

An array containing the read and write halves of the stream.




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

### read



```php
public read(?int $maxBytes = null): \venndev\vosaka\core\Result
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$maxBytes` | **?int** |  |





***

### readExact



```php
public readExact(int $bytes): \venndev\vosaka\core\Result
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$bytes` | **int** |  |





***

### readUntil



```php
public readUntil(string $delimiter): \venndev\vosaka\core\Result
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$delimiter` | **string** |  |





***

### readLine



```php
public readLine(): \venndev\vosaka\core\Result
```












***

### write



```php
public write(string $data): \venndev\vosaka\core\Result
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$data` | **string** |  |





***

### writeAll



```php
public writeAll(string $data): \venndev\vosaka\core\Result
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$data` | **string** |  |





***

### flush



```php
public flush(): \venndev\vosaka\core\Result
```












***

### handleRead



```php
public handleRead(): void
```




* This method is **abstract**.







***

### handleWrite



```php
public handleWrite(): void
```




* This method is **abstract**.







***

### peerAddr



```php
public peerAddr(): string
```




* This method is **abstract**.







***

### isClosed



```php
public isClosed(): bool
```












***

### close



```php
public close(): void
```












***


***
> Automatically generated on 2025-07-16
