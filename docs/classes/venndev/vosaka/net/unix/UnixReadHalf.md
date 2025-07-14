***

# UnixReadHalf





* Full name: `\venndev\vosaka\net\unix\UnixReadHalf`
* Parent class: [`\venndev\vosaka\net\StreamBase`](../StreamBase.md)
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### stream



```php
private \venndev\vosaka\net\unix\UnixStream $stream
```






***

## Methods


### __construct



```php
public __construct(\venndev\vosaka\net\unix\UnixStream $stream): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$stream` | **\venndev\vosaka\net\unix\UnixStream** |  |





***

### handleRead

Handles reading data from the Unix socket.

```php
public handleRead(): void
```

This method is called by the event loop when the socket is ready for reading.










***

### handleWrite

Handles write operations for the Unix read half.

```php
public handleWrite(): void
```

This is a no-op since this is a read-only stream.










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

### read

Reads data from the stream.

```php
public read(int|null $maxBytes = null): \venndev\vosaka\core\Result
```

If no maxBytes is specified, it reads up to the buffer size.






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$maxBytes` | **int&#124;null** | Maximum number of bytes to read. |


**Return Value:**

The result containing the read data or null if closed.




***

### readExact

Reads an exact number of bytes from the stream.

```php
public readExact(int $bytes): \venndev\vosaka\core\Result
```

If the stream is closed before reading the exact bytes, an exception is thrown.






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$bytes` | **int** | Number of bytes to read. |


**Return Value:**

The result containing the read data.




***

### readUntil

Reads data from the stream until a specific delimiter is encountered.

```php
public readUntil(string $delimiter): \venndev\vosaka\core\Result
```

If the delimiter is not found before the read timeout, an exception is thrown.






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$delimiter` | **string** | The delimiter to read until. |


**Return Value:**

The result containing the read data up to the delimiter.




***

### write

Reads a single line from the stream.

```php
public write(string $data): \venndev\vosaka\core\Result
```

This method reads until a newline character is encountered.






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$data` | **string** |  |


**Return Value:**

The result containing the read line or null if closed.




***

### writeAll

Writes all data to the stream.

```php
public writeAll(string $data): \venndev\vosaka\core\Result
```

This method is asynchronous and returns a Result object.






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$data` | **string** | The data to write. |


**Return Value:**

The result of the write operation.




***

### flush

Writes data until the stream is closed.

```php
public flush(): \venndev\vosaka\core\Result
```

This method is not supported for read-only streams and will throw an exception.







**Return Value:**

The result containing the number of bytes written.




***

### close

Closes the stream and removes it from the event loop.

```php
public close(): void
```

This method should be called when the stream is no longer needed.










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
> Automatically generated on 2025-07-14
