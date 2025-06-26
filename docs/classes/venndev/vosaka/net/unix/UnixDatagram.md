***

# UnixDatagram





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
private __construct(): mixed
```












***

### new



```php
public static new(): self
```



* This method is **static**.








***

### bind

Bind the socket to the specified Unix domain socket path

```php
public bind(string $path): \venndev\vosaka\core\Result&lt;\venndev\vosaka\net\unix\UnixDatagram&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | Path to the Unix socket file |





***

### sendTo

Send data to a specific Unix socket path

```php
public sendTo(string $data, string $path): \venndev\vosaka\core\Result&lt;int&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$data` | **string** | Data to send |
| `$path` | **string** | Path to the Unix socket file |


**Return Value:**

Number of bytes sent




***

### receiveFrom

Receive data from a Unix socket

```php
public receiveFrom(int $maxLength = 65535): \venndev\vosaka\core\Result&lt;array{data: string, peerPath: string}&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$maxLength` | **int** | Maximum number of bytes to receive |





***

### setReuseAddr



```php
public setReuseAddr(bool $reuseAddr): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$reuseAddr` | **bool** |  |





***

### validatePath



```php
private validatePath(string $path): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** |  |





***

### createContext



```php
private createContext(): mixed
```












***

### configureSocket



```php
private configureSocket(): void
```












***

### getLocalPath



```php
public getLocalPath(): string
```












***

### close



```php
public close(): void
```












***

### isClosed



```php
public isClosed(): bool
```












***


***
> Automatically generated on 2025-06-26
