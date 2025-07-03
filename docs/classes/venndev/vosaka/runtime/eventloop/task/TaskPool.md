***

# TaskPool





* Full name: `\venndev\vosaka\runtime\eventloop\task\TaskPool`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### pool



```php
private \WeakMap $pool
```






***

### maxPoolSize



```php
private int $maxPoolSize
```






***

### created



```php
private int $created
```






***

### reused



```php
private int $reused
```






***

## Methods


### __construct



```php
public __construct(int $maxPoolSize = 1000): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$maxPoolSize` | **int** |  |





***

### getTask



```php
public getTask(callable $callback, mixed $context = null): \venndev\vosaka\runtime\eventloop\task\Task
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$callback` | **callable** |  |
| `$context` | **mixed** |  |





***

### returnTask



```php
public returnTask(\venndev\vosaka\runtime\eventloop\task\Task $task): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$task` | **\venndev\vosaka\runtime\eventloop\task\Task** |  |





***

### getStats



```php
public getStats(): array
```












***


***
> Automatically generated on 2025-07-03
