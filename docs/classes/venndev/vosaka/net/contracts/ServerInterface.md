***

# ServerInterface

Interface for server/listener sockets



* Full name: `\venndev\vosaka\net\contracts\ServerInterface`



## Methods


### accept

Accept incoming connection

```php
public accept(float $timeout = 0.0): \venndev\vosaka\core\Result&lt;\venndev\vosaka\net\contracts\ConnectionInterface|null&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$timeout` | **float** | Timeout in seconds, 0 for non-blocking |





***

### close

Close the server

```php
public close(): void
```












***

### isClosed

Check if server is closed

```php
public isClosed(): bool
```












***

### getAddress

Get server address

```php
public getAddress(): \venndev\vosaka\net\contracts\AddressInterface
```












***

### getOptions

Get server options

```php
public getOptions(): array
```












***


***
> Automatically generated on 2025-07-24
