***

# Defer

Defer class for handling deferred execution of callbacks in the event loop.

This class allows you to schedule callbacks to be executed later, typically
used for cleanup operations or tasks that should run after the current
task completes. Supports callables, Closures, and Generators.

* Full name: `\venndev\vosaka\core\Defer`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### callback



```php
public mixed $callback
```






***

## Methods


### __construct

Constructor for Defer instruction.

```php
public __construct(mixed $callback): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$callback` | **mixed** | The callback to defer (callable, Closure, or Generator) |




**Throws:**
<p>If the callback is not a valid type</p>

- [`InvalidArgumentException`](../../../InvalidArgumentException.md)



***

### new

Create a Defer instance with the specified callback.

```php
public static new(callable $callback): \venndev\vosaka\core\Defer
```

This is a factory method that provides a convenient way to create
Defer instances. The 'c' stands for 'create'.

* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$callback` | **callable** | The callback to defer for later execution |


**Return Value:**

A new Defer instance




***


***
> Automatically generated on 2025-07-14
