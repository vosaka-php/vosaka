***

# RwLock

RwLock - Reader-Writer Lock implementation using Generator

This class provides a Reader-Writer lock mechanism that allows:
- Multiple readers to access the resource simultaneously
- Only one writer to access the resource at a time
- Writers have priority over readers to prevent writer starvation

The implementation uses Generator-based coroutines to provide
non-blocking asynchronous locking behavior.

* Full name: `\venndev\vosaka\sync\RwLock`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### readerCount



```php
private int $readerCount
```






***

### writerActive



```php
private bool $writerActive
```






***

### readerQueue



```php
private \SplQueue $readerQueue
```






***

### writerQueue



```php
private \SplQueue $writerQueue
```






***

### waitingWriters



```php
private int $waitingWriters
```






***

## Methods


### __construct



```php
public __construct(): mixed
```












***

### new

Create a new instance of RwLock

```php
public static new(): \venndev\vosaka\sync\RwLock
```

This method is used to create a new RwLock instance.

* This method is **static**.








***

### read

Acquire a read lock

```php
public read(): \Generator&lt;mixed,\venndev\vosaka\sync\rwlock\ReadLockGuard&gt;
```

Allows multiple readers to acquire the lock simultaneously unless
there are waiting writers (to prevent writer starvation).










***

### write

Acquire a write lock

```php
public write(): \Generator&lt;mixed,\venndev\vosaka\sync\rwlock\WriteLockGuard&gt;
```

Only one writer can hold the lock at a time, and writers have
priority over readers to prevent starvation.










***

### tryRead

Try to acquire a read lock without blocking

```php
public tryRead(): \venndev\vosaka\core\Result&lt;\venndev\vosaka\sync\rwlock\ReadLockGuard&gt;
```












***

### tryWrite

Try to acquire a write lock without blocking

```php
public tryWrite(): \venndev\vosaka\core\Result&lt;\venndev\vosaka\sync\rwlock\WriteLockGuard&gt;
```












***

### releaseRead

Release a read lock (internal use)

```php
public releaseRead(): void
```












***

### releaseWrite

Release a write lock (internal use)

```php
public releaseWrite(): void
```












***

### getStatus

Get current lock status

```php
public getStatus(): array
```












***

### waitForResolution

Wait for promise resolution (simplified implementation)

```php
private waitForResolution(mixed $promise): \Generator
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$promise` | **mixed** |  |





***


***
> Automatically generated on 2025-07-08
