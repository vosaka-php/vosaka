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

### nextId



```php
private static int $nextId
```



* This property is **static**.


***

### id



```php
private int $id
```






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

Creates a new channel instance.

```php
public static new(int|null $capacity = null): self
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$capacity` | **int&#124;null** | The maximum number of items the channel can hold. |





***

### send

Sends data to the channel.

```php
public send(mixed $data): \venndev\vosaka\core\Result
```

If the channel is full, it will block until space is available.






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$data` | **mixed** | The data to be sent. |





***

### receive

Receives data from the channel.

```php
public receive(): \venndev\vosaka\core\Result
```

If the channel is empty, it will block until data is available.










***

### close

Closes the channel.

```php
public close(): void
```

This will remove the channel from the list of active channels.










***


***
> Automatically generated on 2025-07-16
