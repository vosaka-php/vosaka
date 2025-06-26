***

# Repeat

Repeat class for executing recurring asynchronous operations.

This class provides functionality to repeatedly execute a callback or generator
at specified intervals. The execution runs in its own spawned task and can be
cancelled at any time using the built-in cancellation token. Supports both
regular callables and generator functions.

The repeat operation continues indefinitely until explicitly cancelled,
making it suitable for periodic tasks, monitoring operations, or any
recurring background work that needs to run alongside other async tasks.

* Full name: `\venndev\vosaka\time\Repeat`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### callback



```php
private \Generator|\Closure $callback
```






***

### cancelToken



```php
private \venndev\vosaka\sync\CancelToken $cancelToken
```






***

## Methods


### __construct

Constructor for Repeat.

```php
public __construct(callable|\Generator|\Closure $callback): mixed
```

Creates a new Repeat instance with the specified callback. The callback
can be any callable, a Closure, or a Generator. Callables are converted
to Closures for consistent handling. A cancellation token is automatically
created to allow stopping the repeat operation.






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$callback` | **callable&#124;\Generator&#124;\Closure** | The callback to execute repeatedly |





***

### c

Create a new Repeat instance (factory method).

```php
public static c(callable|\Generator|\Closure $callback): self
```

Convenience factory method for creating Repeat instances.
The 'c' stands for 'create' and provides a shorter syntax
for creating repeating operations.

* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$callback` | **callable&#124;\Generator&#124;\Closure** | The callback to execute repeatedly |


**Return Value:**

A new Repeat instance




***

### __invoke

Start the repeat operation with the specified interval.

```php
public __invoke(int $seconds): self
```

Makes the Repeat instance callable and starts the repeating execution
with the given interval in seconds. The callback will be executed
repeatedly with the specified delay between executions. The operation
runs in a separate spawned task and continues until cancelled.

The method handles different callback types appropriately:
- Closures are called directly
- Generators are yielded from to allow async operations
- Other callables are invoked using call_user_func






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$seconds` | **int** | The interval between executions in seconds |


**Return Value:**

This Repeat instance for method chaining




***

### cancel

Cancel the repeat operation.

```php
public cancel(): void
```

Stops the repeating execution by cancelling the internal cancellation
token. The current execution cycle will complete, but no further
executions will be scheduled. This provides a clean way to stop
long-running repeat operations.










***


***
> Automatically generated on 2025-06-26
