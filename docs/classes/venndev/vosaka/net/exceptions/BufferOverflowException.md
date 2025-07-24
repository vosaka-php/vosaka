***

# BufferOverflowException

Exception thrown when buffer overflow occurs



* Full name: `\venndev\vosaka\net\exceptions\BufferOverflowException`
* Parent class: [`\venndev\vosaka\net\exceptions\NetworkException`](./NetworkException.md)



## Properties


### bufferSize



```php
private int $bufferSize
```






***

### dataSize



```php
private int $dataSize
```






***

## Methods


### __construct



```php
public __construct(int $bufferSize, int $dataSize): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$bufferSize` | **int** |  |
| `$dataSize` | **int** |  |





***

### getBufferSize



```php
public getBufferSize(): int
```












***

### getDataSize



```php
public getDataSize(): int
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
