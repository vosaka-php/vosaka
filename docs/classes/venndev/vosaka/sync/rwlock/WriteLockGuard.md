***

# WriteLockGuard

Write Lock Guard - automatically releases write lock when destroyed



* Full name: `\venndev\vosaka\sync\rwlock\WriteLockGuard`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### lock



```php
private ?\venndev\vosaka\sync\RwLock $lock
```






***

## Methods


### __construct



```php
public __construct(\venndev\vosaka\sync\RwLock $lock): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$lock` | **\venndev\vosaka\sync\RwLock** |  |





***

### __destruct



```php
public __destruct(): mixed
```












***

### new

Create a new WriteLockGuard instance

```php
public static new(\venndev\vosaka\sync\RwLock $lock): self
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$lock` | **\venndev\vosaka\sync\RwLock** | The RwLock instance to guard |





***

### release

Manually release the write lock

```php
public release(): void
```












***

### isHeld

Check if the lock is still held

```php
public isHeld(): bool
```












***


***
> Automatically generated on 2025-07-14
