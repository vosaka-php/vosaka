***

# ConnectionException

Exception thrown when connection operations fail



* Full name: `\venndev\vosaka\net\exceptions\ConnectionException`
* Parent class: [`\venndev\vosaka\net\exceptions\NetworkException`](./NetworkException.md)



## Properties


### host



```php
private ?string $host
```






***

### port



```php
private ?int $port
```






***

## Methods


### setEndpoint



```php
public setEndpoint(string $host, int $port): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$host` | **string** |  |
| `$port` | **int** |  |





***

### getHost



```php
public getHost(): ?string
```












***

### getPort



```php
public getPort(): ?int
```












***


## Inherited methods


### __construct



```php
public __construct(string $message = &quot;&quot;, int $code, ?\Exception $previous = null, array $context = []): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$message` | **string** |  |
| `$code` | **int** |  |
| `$previous` | **?\Exception** |  |
| `$context` | **array** |  |





***

### getContext



```php
public getContext(): array
```












***


***
> Automatically generated on 2025-07-24
