***

# ChildProcessHandler

Handles child process PID cleanup



* Full name: `\venndev\vosaka\cleanup\handler\ChildProcessHandler`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### childPids



```php
private array $childPids
```






***

### logger



```php
private \venndev\vosaka\cleanup\logger\LoggerInterface $logger
```






***

### isWindows



```php
private bool $isWindows
```






***

## Methods


### __construct



```php
public __construct(\venndev\vosaka\cleanup\logger\LoggerInterface $logger, bool $isWindows): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$logger` | **\venndev\vosaka\cleanup\logger\LoggerInterface** |  |
| `$isWindows` | **bool** |  |





***

### addChildProcess



```php
public addChildProcess(int $pid): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$pid` | **int** |  |





***

### removeChildProcessPid



```php
public removeChildProcessPid(string $pid): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$pid` | **string** |  |





***

### cleanupAll



```php
public cleanupAll(): void
```












***

### getChildPids



```php
public getChildPids(): array
```












***

### getCount



```php
public getCount(): int
```












***


***
> Automatically generated on 2025-07-03
