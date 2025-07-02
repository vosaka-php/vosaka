***

# Channel

A simple MPSC (Multiple Producer Single Consumer) channel implementation.

This channel allows multiple producers to send data to a single consumer.
It supports a fixed capacity, and blocks the producer if the channel is full.

* Full name: `\venndev\vosaka\sync\Channel`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### channels



```php
private static array $channels
```



* This property is **static**.


***

### id



```php
private int $id
```






***

### nextId



```php
private static int $nextId
```



* This property is **static**.


***

### capacity



```php
private ?int $capacity
```






***

## Methods


### __construct



```php
public __construct(?int $capacity = null): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$capacity` | **?int** |  |





***

### new



```php
public static new(?int $capacity = null): self
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$capacity` | **?int** |  |





***

### send



```php
public send(mixed $data): \venndev\vosaka\core\Result
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$data` | **mixed** |  |





***

### receive



```php
public receive(): \venndev\vosaka\core\Result
```












***

### close



```php
public close(): void
```












***


***
> Automatically generated on 2025-07-02
