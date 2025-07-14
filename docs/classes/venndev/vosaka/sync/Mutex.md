***

# Mutex

Returns Result<MutexGuard, Error> for lock operations
Uses RAII-style MutexGuard for automatic cleanup
Provides try_lock() that returns Option<MutexGuard>
Uses unwrap() and expect() for error handling



* Full name: `\venndev\vosaka\sync\Mutex`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### locked



```php
private bool $locked
```






***

### owner



```php
private ?string $owner
```






***

### waitingQueue



```php
private array $waitingQueue
```






***

### waitingCount



```php
private int $waitingCount
```






***

## Methods


### new

Create a new Mutex

```php
public static new(): self
```



* This method is **static**.








***

### lock

Lock the mutex and return a MutexGuard

```php
public lock(string|null $taskId = null): \Generator&lt;\venndev\vosaka\core\interfaces\ResultType&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$taskId` | **string&#124;null** | Optional task identifier |





***

### tryLock

Try to lock the mutex without waiting

```php
public tryLock(string|null $taskId = null): \venndev\vosaka\core\interfaces\Option
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$taskId` | **string&#124;null** | Optional task identifier |





***

### lockTimeout

Lock with timeout

```php
public lockTimeout(float $timeoutSeconds, string|null $taskId = null): \Generator&lt;\venndev\vosaka\core\interfaces\ResultType&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$timeoutSeconds` | **float** | Maximum time to wait |
| `$taskId` | **string&#124;null** | Optional task identifier |





***

### forceRelease

Internal method to force release (called by MutexGuard)

```php
public forceRelease(string $taskId): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$taskId` | **string** |  |





***

### isLocked

Check if mutex is locked

```php
public isLocked(): bool
```












***

### owner

Get current owner as Option<string>

```php
public owner(): \venndev\vosaka\core\interfaces\Option
```












***

### waitingCount

Get waiting count

```php
public waitingCount(): int
```












***

### generateTaskId



```php
private generateTaskId(): string
```












***

### removeFromQueue



```php
private removeFromQueue(string $taskId): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$taskId` | **string** |  |





***


***
> Automatically generated on 2025-07-14
