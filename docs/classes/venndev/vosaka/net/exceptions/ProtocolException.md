***

# ProtocolException

Exception thrown for protocol-specific errors



* Full name: `\venndev\vosaka\net\exceptions\ProtocolException`
* Parent class: [`\venndev\vosaka\net\exceptions\NetworkException`](./NetworkException.md)



## Properties


### protocol



```php
private string $protocol
```






***

## Methods


### __construct



```php
public __construct(string $protocol, string $message): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$protocol` | **string** |  |
| `$message` | **string** |  |





***

### getProtocol



```php
public getProtocol(): string
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
