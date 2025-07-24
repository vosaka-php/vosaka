***

# BindException

Exception thrown when bind operations fail



* Full name: `\venndev\vosaka\net\exceptions\BindException`
* Parent class: [`\venndev\vosaka\net\exceptions\NetworkException`](./NetworkException.md)



## Properties


### address



```php
private ?string $address
```






***

## Methods


### setAddress



```php
public setAddress(string $address): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$address` | **string** |  |





***

### getAddress



```php
public getAddress(): ?string
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
