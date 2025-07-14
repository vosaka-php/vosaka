***

# CBreaker

Circuit Breaker implementation to prevent cascading failures in distributed systems.

It allows a certain number of failures before opening the circuit and preventing further calls.

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

### new

Factory method to create a new instance of CBreaker.

```php
public static new(int $threshold, int $timeout): \venndev\vosaka\breaker\CBreaker
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$threshold` | **int** | The number of failures before the circuit opens. |
| `$timeout` | **int** | The time in seconds after which the circuit resets. |





***

### allow

Checks if the circuit breaker allows the execution of a task.

```php
public allow(): bool
```









**Return Value:**

True if the task can be executed, false if the circuit is open.




***

### recordFailure

Records a failure in the circuit breaker.

```php
public recordFailure(): void
```

This increments the failure count and updates the last failure time.










***

### reset

Resets the circuit breaker, clearing the failure count and last failure time.

```php
public reset(): void
```












***

### call

Calls a task and manages the circuit breaker state.

```php
public call(\Generator $task): \venndev\vosaka\core\Result
```

If the circuit is open, it throws an exception.
If the task fails, it records the failure.






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$task` | **\Generator** | The task to be executed. |


**Return Value:**

The result of the task execution.



**Throws:**
<p>if the circuit breaker is open.</p>

- [`RuntimeException`](../../../RuntimeException.md)



***


***
> Automatically generated on 2025-07-14
