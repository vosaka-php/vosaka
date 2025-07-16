***

# Loopify





* Full name: `\venndev\vosaka\task\Loopify`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### callback



```php
private mixed $callback
```






***

### cancelToken



```php
private \venndev\vosaka\sync\CancelToken $cancelToken
```






***

### interval



```php
private float $interval
```






***

### maps



```php
private array $maps
```






***

## Methods


### __construct



```php
public __construct(\Generator|callable $callback): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$callback` | **\Generator&#124;callable** |  |





***

### new

Factory method to create a new Loopify instance.

```php
public static new(\Generator|callable $callback): self
```

This method allows you to create a new Loopify instance with a callback
that can be a Generator or a callable. It provides a convenient way
to instantiate the Loopify without needing to use the constructor directly.

* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$callback` | **\Generator&#124;callable** | The callback to execute in the loop |


**Return Value:**

A new instance of Loopify




***

### interval

Set the interval for the loop execution.

```php
public interval(float $interval): self
```

This method allows you to specify a time interval (in seconds) between
each execution of the callback. If the interval is set to 0.0, the loop
will execute as fast as possible without any delay.






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$interval` | **float** | The interval in seconds |


**Return Value:**

The current instance for method chaining




***

### map

Map a callback to the result of the loop.

```php
public map(callable $callback): self
```

This method allows you to apply a transformation to the result of the
loop's callback. The provided callback will be called with the result
of the loop, and its return value will be used as the new result.






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$callback` | **callable** | The mapping function to apply |


**Return Value:**

The current instance for method chaining




***

### cancel

Cancel the loop execution.

```php
public cancel(): void
```

This method allows you to cancel the loop execution. Once called, the
loop will stop executing further iterations and will exit gracefully.










***

### wait

Wait for the loop to complete and return the result.

```php
public wait(): \venndev\vosaka\core\Result
```

This method starts the loop execution and returns a Future that will
resolve with the final result of the loop. The loop will continue to
execute until it is cancelled or the callback completes.

The result can be transformed using any mapping functions provided
through the `map` method.







**Return Value:**

A Future that resolves with the final result of the loop




***


***
> Automatically generated on 2025-07-16
