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

### tick



```php
public tick(): bool
```












***


***
> Automatically generated on 2025-07-02
