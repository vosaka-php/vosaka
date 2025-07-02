***

# CallbackHandler

Handles cleanup callbacks



* Full name: `\venndev\vosaka\cleanup\handler\CallbackHandler`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### cleanupCallbacks



```php
private array $cleanupCallbacks
```






***

### logger



```php
private \venndev\vosaka\cleanup\logger\LoggerInterface $logger
```






***

## Methods


### __construct



```php
public __construct(\venndev\vosaka\cleanup\logger\LoggerInterface $logger): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$logger` | **\venndev\vosaka\cleanup\logger\LoggerInterface** |  |





***

### addCleanupCallback



```php
public addCleanupCallback(callable $callback): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$callback` | **callable** |  |





***

### executeCallbacks



```php
public executeCallbacks(): void
```












***

### getCount



```php
public getCount(): int
```












***


***
> Automatically generated on 2025-07-02
