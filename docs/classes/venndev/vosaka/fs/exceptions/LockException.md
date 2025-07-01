***

# LockException

Exception thrown when file locking operations fail.

This exception is thrown when attempting to acquire, release, or manage
file locks fails due to contention, system limitations, or other
locking-related issues.

* Full name: `\venndev\vosaka\fs\exceptions\LockException`
* Parent class: [`\venndev\vosaka\fs\exceptions\FileSystemException`](./FileSystemException.md)



## Properties


### lockOperation

The type of lock operation that failed.

```php
protected string $lockOperation
```






***

### lockType

The lock type (shared, exclusive, etc.).

```php
protected ?string $lockType
```






***

### lockHolderPid

The process ID that currently holds the lock (if known).

```php
protected ?int $lockHolderPid
```






***

### timeout

The timeout value for the lock operation (if applicable).

```php
protected ?int $timeout
```






***

## Methods


### __construct

LockException constructor.

```php
public __construct(string $path, string $lockOperation, string $operation = &quot;file operation&quot;, string|null $lockType = null, int|null $lockHolderPid = null, int|null $timeout = null, array $context = [], int $code, \Exception|null $previous = null): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The path where the lock operation failed |
| `$lockOperation` | **string** | The specific lock operation that failed |
| `$operation` | **string** | The higher-level operation being performed |
| `$lockType` | **string&#124;null** | The type of lock |
| `$lockHolderPid` | **int&#124;null** | PID of current lock holder |
| `$timeout` | **int&#124;null** | Lock timeout value |
| `$context` | **array** | Additional context information |
| `$code` | **int** | The exception code |
| `$previous` | **\Exception&#124;null** | The previous exception |





***

### getLockOperation

Get the lock operation that failed.

```php
public getLockOperation(): string
```









**Return Value:**

The lock operation




***

### getLockType

Get the lock type.

```php
public getLockType(): string|null
```









**Return Value:**

The lock type




***

### getLockHolderPid

Get the PID of the process holding the lock.

```php
public getLockHolderPid(): int|null
```









**Return Value:**

The lock holder PID




***

### getTimeout

Get the timeout value for the lock operation.

```php
public getTimeout(): int|null
```









**Return Value:**

The timeout value




***

### forAcquire

Create a LockException for acquire operations.

```php
public static forAcquire(string $path, string $lockType = &quot;exclusive&quot;, string $operation = &quot;acquire lock&quot;, int|null $timeout = null, array $context = []): static
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The file path |
| `$lockType` | **string** | The lock type (shared, exclusive) |
| `$operation` | **string** | The operation being performed |
| `$timeout` | **int&#124;null** | Lock timeout |
| `$context` | **array** | Additional context |





***

### forRelease

Create a LockException for release operations.

```php
public static forRelease(string $path, string $operation = &quot;release lock&quot;, array $context = []): static
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The file path |
| `$operation` | **string** | The operation being performed |
| `$context` | **array** | Additional context |





***

### forTimeout

Create a LockException for timeout situations.

```php
public static forTimeout(string $path, int|float $timeout, string $lockType = &quot;exclusive&quot;, string $operation = &quot;acquire lock&quot;, array $context = []): static
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The file path |
| `$timeout` | **int&#124;float** | The timeout value that was exceeded |
| `$lockType` | **string** | The lock type |
| `$operation` | **string** | The operation being performed |
| `$context` | **array** | Additional context |





***

### forContention

Create a LockException for contention situations.

```php
public static forContention(string $path, int|null $lockHolderPid = null, string $lockType = &quot;exclusive&quot;, string $operation = &quot;acquire lock&quot;, array $context = []): static
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The file path |
| `$lockHolderPid` | **int&#124;null** | PID of the process holding the lock |
| `$lockType` | **string** | The lock type |
| `$operation` | **string** | The operation being performed |
| `$context` | **array** | Additional context |





***

### forDeadlock

Create a LockException for deadlock situations.

```php
public static forDeadlock(string $path, array $involvedPids = [], string $operation = &quot;acquire lock&quot;, array $context = []): static
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The file path |
| `$involvedPids` | **array** | PIDs involved in the deadlock |
| `$operation` | **string** | The operation being performed |
| `$context` | **array** | Additional context |





***

### forStaleLock

Create a LockException for stale lock situations.

```php
public static forStaleLock(string $path, int|null $stalePid = null, string $operation = &quot;acquire lock&quot;, array $context = []): static
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The file path |
| `$stalePid` | **int&#124;null** | PID of the stale lock holder |
| `$operation` | **string** | The operation being performed |
| `$context` | **array** | Additional context |





***

### forInvalidOperation

Create a LockException for invalid lock operations.

```php
public static forInvalidOperation(string $path, string $reason, string $operation = &quot;lock operation&quot;, array $context = []): static
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The file path |
| `$reason` | **string** | The reason why the lock operation is invalid |
| `$operation` | **string** | The operation being performed |
| `$context` | **array** | Additional context |





***

### withMessage

Create a LockException with custom message.

```php
public static withMessage(string $message, string $path, string $lockOperation, string $operation = &quot;file operation&quot;, array $context = []): static
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$message` | **string** | Custom error message |
| `$path` | **string** | The file path |
| `$lockOperation` | **string** | The lock operation |
| `$operation` | **string** | The higher-level operation |
| `$context` | **array** | Additional context |





***


## Inherited methods


### __construct

FileSystemException constructor.

```php
public __construct(string $message, string $path = &#039;&#039;, string $operation = &#039;&#039;, array $context = [], int $code, \Exception|null $previous = null): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$message` | **string** | The exception message |
| `$path` | **string** | The path that caused the exception |
| `$operation` | **string** | The operation being performed |
| `$context` | **array** | Additional context information |
| `$code` | **int** | The exception code |
| `$previous` | **\Exception&#124;null** | The previous exception |





***

### getPath

Get the path that caused the exception.

```php
public getPath(): string
```









**Return Value:**

The path




***

### getOperation

Get the operation that was being performed.

```php
public getOperation(): string
```









**Return Value:**

The operation




***

### getContext

Get additional context information.

```php
public getContext(): array
```









**Return Value:**

The context




***

### getFormattedMessage

Get a formatted string representation of the exception.

```php
public getFormattedMessage(): string
```









**Return Value:**

The formatted exception string




***


***
> Automatically generated on 2025-07-01
