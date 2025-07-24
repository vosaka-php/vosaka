***

# TCPServer

TCP Server implementation



* Full name: `\venndev\vosaka\net\tcp\TCPServer`
* This class implements:
[`\venndev\vosaka\net\contracts\ServerInterface`](../contracts/ServerInterface.md)



## Properties


### socket



```php
private $socket
```






***

### closed



```php
private bool $closed
```






***

### address



```php
private \venndev\vosaka\net\tcp\TCPAddress $address
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
public __construct(mixed $socket, \venndev\vosaka\net\tcp\TCPAddress $address, array $options = []): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$socket` | **mixed** |  |
| `$address` | **\venndev\vosaka\net\tcp\TCPAddress** |  |
| `$options` | **array** |  |





***

### accept

Accept a new connection

```php
public accept(float $timeout = 0.0): \venndev\vosaka\core\Result&lt;\venndev\vosaka\net\tcp\TCPConnection|null&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$timeout` | **float** | Timeout in seconds, 0 for no timeout |





***

### doAccept

Internal method to handle accepting connections

```php
private doAccept(float $timeout): \Generator&lt;\venndev\vosaka\net\tcp\TCPConnection|null&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$timeout` | **float** | Timeout in seconds, 0 for no timeout |




**Throws:**

- [`NetworkException`](../exceptions/NetworkException.md)



***

### close

Close the server socket

```php
public close(): void
```












***

### isClosed

Check if the server is closed

```php
public isClosed(): bool
```












***

### getAddress

Get the address this server is bound to

```php
public getAddress(): \venndev\vosaka\net\contracts\AddressInterface
```












***

### getOptions

Get the options used for this server

```php
public getOptions(): array
```












***


***
> Automatically generated on 2025-07-24
