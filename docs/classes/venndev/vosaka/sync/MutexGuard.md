***

# MutexGuard

MutexGuard - RAII-style lock guard



* Full name: `\venndev\vosaka\sync\MutexGuard`



## Properties


### mutex



```php
private \venndev\vosaka\sync\Mutex $mutex
```






***

### taskId



```php
private string $taskId
```






***

### released



```php
private bool $released
```






***

## Methods


### __construct



```php
public __construct(\venndev\vosaka\sync\Mutex $mutex, string $taskId): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$mutex` | **\venndev\vosaka\sync\Mutex** |  |
| `$taskId` | **string** |  |





***

### __destruct



```php
public __destruct(): mixed
```












***

### new

Create a new MutexGuard instance

```php
public static new(\venndev\vosaka\sync\Mutex $mutex, string $taskId): \venndev\vosaka\sync\MutexGuard
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$mutex` | **\venndev\vosaka\sync\Mutex** | The mutex to guard |
| `$taskId` | **string** | The ID of the task that holds the lock |





***

### drop

Explicitly release the lock

```php
public drop(): void
```












***


***
> Automatically generated on 2025-07-04
