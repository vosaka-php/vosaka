***

# Sleep

Sleep class for handling asynchronous sleep operations in the event loop.

This class provides various methods to create sleep instructions with different
time units (seconds, milliseconds, microseconds) that can be yielded in generators
to pause execution without blocking the event loop.

* Full name: `\venndev\vosaka\time\Sleep`
* This class is marked as **final** and can't be subclassed
* This class implements:
[`\venndev\vosaka\core\interfaces\Time`](../core/interfaces/Time.md)
* This class is a **Final class**



## Properties


### seconds



```php
public float $seconds
```






***

## Methods


### __construct

Constructor for Sleep instruction.

```php
public __construct(float $seconds): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$seconds` | **float** | The number of seconds to sleep (can be fractional) |





***

### new

Create a Sleep instance with the specified number of seconds.

```php
public static new(float $seconds): self
```

This is a factory method that provides a convenient way to create
Sleep instances. The 'c' stands for 'create'.

* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$seconds` | **float** | The number of seconds to sleep (can be fractional) |


**Return Value:**

A new Sleep instance




***

### ms

Create a Sleep instance with the specified number of milliseconds.

```php
public static ms(int $milliseconds): self
```

Converts milliseconds to seconds for internal storage.

* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$milliseconds` | **int** | The number of milliseconds to sleep |


**Return Value:**

A new Sleep instance




***

### us

Create a Sleep instance with the specified number of microseconds.

```php
public static us(int $microseconds): self
```

Converts microseconds to seconds for internal storage.

* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$microseconds` | **int** | The number of microseconds to sleep |


**Return Value:**

A new Sleep instance




***

### toGenerator

Convert the sleep instruction to a generator.

```php
public toGenerator(): \Generator
```

This method yields control back to the event loop for the specified
duration, allowing other tasks to run while waiting.







**Return Value:**

A generator that yields until the sleep duration is complete




***


***
> Automatically generated on 2025-07-24
