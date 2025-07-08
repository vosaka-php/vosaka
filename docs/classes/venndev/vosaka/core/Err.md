***

# Err





* Full name: `\venndev\vosaka\core\Err`
* Parent class: [`\venndev\vosaka\core\interfaces\ResultType`](./interfaces/ResultType.md)
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### error



```php
private $error
```






***

## Methods


### __construct



```php
public __construct(mixed $error): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$error` | **mixed** |  |





***

### isOk



```php
public isOk(): bool
```












***

### isErr



```php
public isErr(): bool
```












***

### unwrap



```php
public unwrap(): mixed
```












***

### unwrapOr



```php
public unwrapOr(mixed $default): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$default` | **mixed** |  |





***


## Inherited methods


### isOk



```php
public isOk(): bool
```




* This method is **abstract**.







***

### isErr



```php
public isErr(): bool
```




* This method is **abstract**.







***

### unwrap



```php
public unwrap(): mixed
```




* This method is **abstract**.







***

### unwrapOr



```php
public unwrapOr(mixed $default): mixed
```




* This method is **abstract**.



**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$default` | **mixed** |  |





***


***
> Automatically generated on 2025-07-08
