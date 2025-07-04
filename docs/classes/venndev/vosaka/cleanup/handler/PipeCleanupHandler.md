***

# PipeCleanupHandler

Handles pipe resource cleanup



* Full name: `\venndev\vosaka\cleanup\handler\PipeCleanupHandler`
* This class is marked as **final** and can't be subclassed
* This class implements:
[`\venndev\vosaka\cleanup\interfaces\CleanupHandlerInterface`](../interfaces/CleanupHandlerInterface.md)
* This class is a **Final class**



## Properties


### pipes



```php
private array $pipes
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

### addPipe



```php
public addPipe(mixed $pipe): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$pipe` | **mixed** |  |





***

### addPipes



```php
public addPipes(array $pipes): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$pipes` | **array** |  |





***

### removePipe



```php
public removePipe(mixed $pipe): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$pipe` | **mixed** |  |





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

### getPipeIds



```php
public getPipeIds(): array
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
> Automatically generated on 2025-07-04
