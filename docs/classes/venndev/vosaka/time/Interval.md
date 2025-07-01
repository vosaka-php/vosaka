***

# Interval

Interval class for handling recurring asynchronous intervals in the event loop.

This class provides various methods to create interval instructions with different
time units (seconds, milliseconds, microseconds) that can be yielded in generators
to create recurring delays without blocking the event loop. Unlike Sleep which
creates a one-time delay, Interval is designed for recurring operations.

The class implements the Time interface and can be used anywhere a time-based
instruction is expected in the VOsaka async runtime.

* Full name: `\venndev\vosaka\time\Interval`
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

Constructor for Interval instruction.

```php
public __construct(float $seconds): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$seconds` | **float** | The interval duration in seconds (can be fractional) |





***

### c

Create an Interval instance with the specified number of seconds.

```php
public static c(float $seconds): self
```

This is a factory method that provides a convenient way to create
Interval instances. The 'c' stands for 'create'.

* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$seconds` | **float** | The interval duration in seconds (can be fractional) |


**Return Value:**

A new Interval instance




***

### ms

Create an Interval instance with the specified number of milliseconds.

```php
public static ms(int $milliseconds): self
```

Converts milliseconds to seconds for internal storage.

* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$milliseconds` | **int** | The interval duration in milliseconds |


**Return Value:**

A new Interval instance




***

### us

Create an Interval instance with the specified number of microseconds.

```php
public static us(int $microseconds): self
```

Converts microseconds to seconds for internal storage.

* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$microseconds` | **int** | The interval duration in microseconds |


**Return Value:**

A new Interval instance




***

### tick

Create an Interval instance with a default tick duration.

```php
public static tick(): self
```

Creates an interval with a duration of 1 millisecond, which is useful
for high-frequency recurring operations or for yielding control to the
event loop very frequently without significant delay.

* This method is **static**.





**Return Value:**

A new Interval instance with 1ms duration




***


***
> Automatically generated on 2025-07-01
