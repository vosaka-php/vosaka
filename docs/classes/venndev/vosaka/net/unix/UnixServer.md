***

# UnixServer

Unix Socket Server implementation



* Full name: `\venndev\vosaka\net\unix\UnixServer`
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
private \venndev\vosaka\net\unix\UnixAddress $address
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
public __construct(mixed $socket, \venndev\vosaka\net\unix\UnixAddress $address, array $options = []): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$socket` | **mixed** |  |
| `$address` | **\venndev\vosaka\net\unix\UnixAddress** |  |
| `$options` | **array** |  |





***

### accept

Accept a new client connection asynchronously.

```php
public accept(float $timeout = 0.0): \venndev\vosaka\core\Result&lt;\venndev\vosaka\net\unix\UnixConnection|null&gt;
```

This method returns a Future that resolves to a UnixConnection
or null if no connection is available within the timeout.






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$timeout` | **float** | Timeout in seconds, 0.0 means no timeout. |





***

### doAccept

Accept a new client connection.

```php
private doAccept(float $timeout): \Generator&lt;\venndev\vosaka\net\unix\UnixConnection|null&gt;
```

This method is a generator that yields a UnixConnection
or null if no connection is available within the timeout.






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$timeout` | **float** | Timeout in seconds, 0.0 means no timeout. |




**Throws:**

- [`NetworkException`](../exceptions/NetworkException.md)



***

### close

Close the server socket and clean up resources.

```php
public close(): void
```












***

### isClosed

Check if the server is closed.

```php
public isClosed(): bool
```












***

### getAddress

Get the address the server is bound to.

```php
public getAddress(): \venndev\vosaka\net\contracts\AddressInterface
```












***

### getOptions

Get the options set for this server.

```php
public getOptions(): array
```












***


***
> Automatically generated on 2025-07-24
