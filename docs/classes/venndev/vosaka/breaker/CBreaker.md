***

# CBreaker





* Full name: `\venndev\vosaka\breaker\CBreaker`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### failureCount



```php
private int $failureCount
```






***

### lastFailureTime



```php
private int $lastFailureTime
```






***

### threshold



```php
private int $threshold
```






***

### timeout



```php
private int $timeout
```






***

## Methods


### __construct



```php
public __construct(int $threshold, int $timeout): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$threshold` | **int** |  |
| `$timeout` | **int** |  |





***

### allow



```php
public allow(): bool
```












***

### recordFailure



```php
public recordFailure(): void
```












***

### reset



```php
public reset(): void
```












***

### call



```php
public call(\Generator $task): \venndev\vosaka\core\Result
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$task` | **\Generator** |  |





***


***
> Automatically generated on 2025-06-26
