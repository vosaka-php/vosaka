***

# TimeoutException

Exception thrown when a timeout occurs



* Full name: `\venndev\vosaka\net\exceptions\TimeoutException`
* Parent class: [`\venndev\vosaka\net\exceptions\NetworkException`](./NetworkException.md)



## Properties


### timeout



```php
private float $timeout
```






***

## Methods


### __construct



```php
public __construct(float $timeout, string $operation = &quot;&quot;): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$timeout` | **float** |  |
| `$operation` | **string** |  |





***

### getTimeout



```php
public getTimeout(): float
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
