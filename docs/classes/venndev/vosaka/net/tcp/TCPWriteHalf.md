***

# TCPWriteHalf





* Full name: `\venndev\vosaka\net\tcp\TCPWriteHalf`
* Parent class: [`\venndev\vosaka\net\StreamBase`](../StreamBase.md)
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


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

### handleRead

Handles reading data from the TCP socket.

```php
public handleRead(): void
```

This is a no-op since this is a write-only stream.










***

### handleWrite

Handles write operations for the TCP stream.

```php
public handleWrite(): void
```

This method is called by the event loop when the socket is ready for writing.










***

### peerAddr

Returns the peer address of the TCP connection.

```php
public peerAddr(): string
```

This is typically the address of the remote host.







**Return Value:**

The peer address.




***

### read

Writes data to the stream.

```php
public read(?int $maxBytes = null): \venndev\vosaka\core\Result
```

This method is asynchronous and returns a Result object.






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$maxBytes` | **?int** |  |


**Return Value:**

The result of the write operation.




***

### readExact

Writes all data to the stream.

```php
public readExact(int $bytes): \venndev\vosaka\core\Result
```

This method is asynchronous and returns a Result object.






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$bytes` | **int** |  |


**Return Value:**

The result of the write operation.




***

### readUntil

Reads data until a specific delimiter is encountered.

```php
public readUntil(string $delimiter): \venndev\vosaka\core\Result
```

This method is asynchronous and returns a Result object.






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$delimiter` | **string** | The delimiter to read until. |


**Return Value:**

The result of the read operation.




***

### readLine

Reads a single line from the stream.

```php
public readLine(): \venndev\vosaka\core\Result
```

This method is asynchronous and returns a Result object.







**Return Value:**

The result of the read operation.




***

### close

Closes the TCP stream.

```php
public close(): void
```

This method will remove the write stream from the event loop if it is registered.










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
