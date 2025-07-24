***

# JoinSetTask

Internal class to track individual tasks in a JoinSet



* Full name: `\venndev\vosaka\task\JoinSetTask`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### aborted



```php
private bool $aborted
```






***

### detached



```php
private bool $detached
```






***

### key



```php
private mixed $key
```






***

### id



```php
private int $id
```






***

### result



```php
private \venndev\vosaka\core\Result $result
```






***

### context



```php
private mixed $context
```






***

## Methods


### __construct



```php
public __construct(int $id, \venndev\vosaka\core\Result $result, mixed $context = null): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$id` | **int** |  |
| `$result` | **\venndev\vosaka\core\Result** |  |
| `$context` | **mixed** |  |





***

### getId



```php
public getId(): int
```












***

### getResult



```php
public getResult(): \venndev\vosaka\core\Result
```












***

### getContext



```php
public getContext(): mixed
```












***

### getKey



```php
public getKey(): mixed
```












***

### setKey



```php
public setKey(mixed $key): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$key` | **mixed** |  |





***

### abort



```php
public abort(): void
```












***

### detach



```php
public detach(): void
```












***

### isAborted



```php
public isAborted(): bool
```












***

### isDetached



```php
public isDetached(): bool
```












***


***
> Automatically generated on 2025-07-24
