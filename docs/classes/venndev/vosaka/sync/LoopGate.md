***

# LoopGate

LoopGate is a simple synchronization primitive that allows
a task to proceed only after a specified number of ticks.

It can be used to control the flow of tasks in a loop.

* Full name: `\venndev\vosaka\sync\LoopGate`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### n



```php
private int $n
```






***

### counter



```php
private int $counter
```






***

## Methods


### __construct



```php
public __construct(int $n): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$n` | **int** |  |





***

### new

Creates a new LoopGate instance.

```php
public static new(int $n): \venndev\vosaka\sync\LoopGate
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$n` | **int** | The number of ticks after which the gate opens. |





***

### tick

Ticks the gate. If the number of ticks reaches `n`, it resets
the counter and returns true, allowing the task to proceed.

```php
public tick(): bool
```









**Return Value:**

True if the gate opens, false otherwise.




***


***
> Automatically generated on 2025-07-03
