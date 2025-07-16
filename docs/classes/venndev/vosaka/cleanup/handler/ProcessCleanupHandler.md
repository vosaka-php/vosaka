***

# ProcessCleanupHandler

Handles process resource cleanup



* Full name: `\venndev\vosaka\cleanup\handler\ProcessCleanupHandler`
* This class is marked as **final** and can't be subclassed
* This class implements:
[`\venndev\vosaka\cleanup\interfaces\CleanupHandlerInterface`](../interfaces/CleanupHandlerInterface.md)
* This class is a **Final class**



## Properties


### processes



```php
private array $processes
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

### addProcess



```php
public addProcess(mixed $process): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$process` | **mixed** |  |





***

### removeProcess



```php
public removeProcess(mixed $process): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$process` | **mixed** |  |





***

### cleanup



```php
public cleanup(): void
```












***

### cleanupAll



```php
public cleanupAll(): void
```












***

### getResourceCount



```php
public getResourceCount(): int
```












***

### getProcessIds



```php
public getProcessIds(): array
```












***

### getResourceId



```php
private getResourceId(mixed $resource): string
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$resource` | **mixed** |  |





***


***
> Automatically generated on 2025-07-16
