***

# CallableUtil

CallableUtil class for utility functions related to callable and generator handling.

This utility class provides methods for converting between different types of
callables and generators, making it easier to work with mixed function types
in the VOsaka async runtime. It handles the conversion of various value types
to callables and provides generator wrapping functionality.

The class is particularly useful for normalizing different types of async
operations into consistent callable formats that can be used throughout
the event loop and task management system.

* Full name: `\venndev\vosaka\utils\CallableUtil`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**




## Methods


### toGenerator

Convert a callable to a generator that yields its result.

```php
public static toGenerator(callable $callable): \Generator
```

Wraps a callable in a generator that calls the function and yields
its return value. This is useful for converting synchronous callables
into async-compatible generators that can be used with the VOsaka
event loop system.

* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$callable` | **callable** | The callable to wrap in a generator |


**Return Value:**

A generator that yields the callable's result




***

### makeAllToCallable

Convert any value to a callable format.

```php
public static makeAllToCallable(mixed $value): callable
```

Normalizes different types of values into callable format for consistent
handling throughout the async runtime. The conversion rules are:
- If already callable, returns as-is
- If a Generator, wraps in a function that returns the generator
- Otherwise, wraps in a function that returns the value

This method is essential for the task system to handle mixed types
of operations uniformly, whether they're functions, generators, or
plain values.

* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$value` | **mixed** | The value to convert to callable format |


**Return Value:**

A callable that produces the appropriate result




***


***
> Automatically generated on 2025-07-16
