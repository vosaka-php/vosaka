***

# Semaphore

Semaphore class for controlling access to shared resources in async contexts.

A semaphore is a synchronization primitive that maintains a count of permits
available for concurrent access to a shared resource. Tasks must acquire a
permit before accessing the resource and release it when done.

This implementation is designed for use with VOsaka's async runtime and
uses yielding to avoid blocking the event loop when waiting for permits.

* Full name: `\venndev\vosaka\sync\Semaphore`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### count



```php
private int $count
```






***

### maxCount



```php
private int $maxCount
```






***

## Methods


### __construct

Constructor for Semaphore.

```php
public __construct(int $maxCount): mixed
```

Creates a new semaphore with the specified maximum number of permits.
The semaphore starts with zero permits acquired, meaning all permits
are initially available.






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$maxCount` | **int** | Maximum number of permits available (must be positive) |




**Throws:**
<p>If maxCount is not positive</p>

- [`InvalidArgumentException`](../../../InvalidArgumentException.md)



***

### acquire

Acquire a permit from the semaphore.

```php
public acquire(): \Generator
```

Attempts to acquire a permit from the semaphore. If no permits are
available (count has reached maxCount), the method will yield control
to the event loop and keep trying until a permit becomes available.

This method is async-safe and will not block the event loop, allowing
other tasks to run while waiting for a permit.







**Return Value:**

Yields until a permit is acquired




***

### release

Release a permit back to the semaphore.

```php
public release(): void
```

Releases a previously acquired permit, making it available for other
tasks to acquire. This decrements the current count of acquired permits.
If no permits are currently acquired, this method has no effect.

Always call release() after you're done with the protected resource
to ensure other tasks can access it.










***

### getCount

Get the current number of acquired permits.

```php
public getCount(): int
```

Returns the number of permits currently acquired by tasks. This value
will be between 0 and maxCount. The number of available permits is
(maxCount - current count).







**Return Value:**

The number of permits currently acquired




***


***
> Automatically generated on 2025-07-03
