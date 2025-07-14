***

# Task





* Full name: `\venndev\vosaka\eventloop\task\Task`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### id



```php
public int $id
```






***

### state



```php
public \venndev\vosaka\eventloop\task\TaskState $state
```






***

### error



```php
public ?\Throwable $error
```






***

### wakeTime



```php
public float $wakeTime
```






***

### callback



```php
public mixed $callback
```






***

### context



```php
public mixed $context
```






***

### firstRun



```php
public bool $firstRun
```






***

### nextId



```php
private static int $nextId
```



* This property is **static**.


***

## Methods


### __construct



```php
public __construct(callable $task, mixed $context = null): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$task` | **callable** |  |
| `$context` | **mixed** |  |





***

### tryWake



```php
public tryWake(): bool
```












***

### sleep



```php
public sleep(float $seconds): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$seconds` | **float** |  |





***

### reset



```php
public reset(): bool
```












***


***
> Automatically generated on 2025-07-14
