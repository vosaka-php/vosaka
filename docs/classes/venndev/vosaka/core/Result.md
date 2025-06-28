***

# Result

Result class for handling asynchronous task results and transformations.

This class wraps generator-based tasks and provides a fluent interface for
result handling, transformation, and error management. It supports:

- Checking if results are successful with isOk()
- Chaining transformations with map()
- Unwrapping results with various error handling strategies
- Providing default values for failed operations

The Result class is central to VOsaka's error handling strategy, allowing
for composable and predictable async operation results.

* Full name: `\venndev\vosaka\core\Result`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### callbacks



```php
private array $callbacks
```






***

### result



```php
private mixed $result
```






***

### task



```php
public \Generator $task
```






***

## Methods


### __construct

Constructor for Result wrapper.

```php
public __construct(\Generator $task): mixed
```

Creates a new Result instance that wraps a Generator task. The task
will be executed when one of the unwrap methods is called, and any
registered callbacks will be applied to transform the result.






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$task` | **\Generator** | The generator task to wrap |





***

### isOk

Check if the result is successful (not an instance of Throwable or Error).

```php
public isOk(): bool
```

Examines the current value of the wrapped task to determine if it
represents a successful result. Returns false if the current value
is any kind of exception or error.







**Return Value:**

True if the result is successful, false otherwise




***

### map

Add a callback to be executed on the result for transformation.

```php
public map(callable $callback): \venndev\vosaka\core\Result
```

Registers a callback that will be executed when the result is unwrapped.
Callbacks are executed in the order they were added, with each callback
receiving the result of the previous callback. Supports method chaining
for composing multiple transformations.

If a callback returns a Generator, it will be properly awaited.
If a callback throws an exception, the transformation chain stops
and the exception becomes the final result.






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$callback` | **callable** | The callback to execute on the result |


**Return Value:**

The current Result instance for method chaining




***

### executeCallbacks

Execute all registered callbacks on the result in sequence.

```php
private executeCallbacks(mixed $result): \Generator
```

Internal method that applies all registered transformation callbacks
to the result in the order they were added. Each callback receives
the output of the previous callback, creating a transformation pipeline.

If any callback returns a Generator, it is properly awaited before
passing its result to the next callback. If any callback throws an
exception, the transformation chain stops and returns the exception.






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$result` | **mixed** | The initial result to transform |


**Return Value:**

The final transformed result or a Throwable if an error occurred




***

### unwrap

Unwrap the result, executing all callbacks and returning the final value.

```php
public unwrap(): \Generator
```

Executes the wrapped task, applies all registered transformation callbacks,
and returns the final result. If the task or any callback produces an
exception, that exception is thrown.

This is the primary method for extracting values from Result instances
when you want exceptions to propagate normally.







**Return Value:**

The final value after executing all callbacks



**Throws:**
<p>If the task fails or any callback throws an exception</p>

- [`Throwable`](../../../Throwable.md)



***

### unwrapOr

Unwrap the result, returning a default value if the result is an error.

```php
public unwrapOr(mixed $default): \Generator
```

Similar to unwrap() but provides graceful error handling by returning
a default value instead of throwing exceptions. Executes the task and
applies all transformation callbacks, but if any step produces an
exception, returns the provided default value instead.

This is useful when you want to provide fallback values for failed
operations without having to handle exceptions explicitly.






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$default` | **mixed** | The default value to return if the result is an error |


**Return Value:**

The final transformed value or the default value




***

### expect

Unwrap the result with a custom error message.

```php
public expect(string $message): \Generator
```

Similar to unwrap() but allows you to provide a custom error message
that will be used if the operation fails. If the task or any callback
produces an exception, a new RuntimeException is thrown with your
custom message, while the original exception is preserved as the cause.

This is useful for providing context-specific error messages that
help with debugging and error reporting.






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$message` | **string** | Custom error message to throw if the result is an error |


**Return Value:**

The final value after executing all callbacks



**Throws:**
<p>If the result is an error, throws with the custom message</p>

- [`RuntimeException`](../../../RuntimeException.md)



***

### __invoke

Invoke the Result as a callable, returning the result or error message.

```php
public __invoke(): \Generator
```

Makes the Result instance callable by implementing __invoke(). When called,
executes the task and applies transformations, but instead of throwing
exceptions, returns the error message as a string if an error occurs.

This provides a convenient way to extract either the successful result
or a string representation of any error that occurred, without having
to handle exceptions.







**Return Value:**

The final result value or the error message string if an error occurred




***


***
> Automatically generated on 2025-06-28
