***

# SocketCleanupHandler

Handles socket resource cleanup



* Full name: `\venndev\vosaka\cleanup\handler\SocketCleanupHandler`
* This class is marked as **final** and can't be subclassed
* This class implements:
[`\venndev\vosaka\cleanup\interfaces\CleanupHandlerInterface`](../interfaces/CleanupHandlerInterface.md)
* This class is a **Final class**



## Properties


### sockets



```php
private array $sockets
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

### addSocket



```php
public addSocket(mixed $socket): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$socket` | **mixed** |  |





***

### removeSocket



```php
public removeSocket(mixed $socket): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$socket` | **mixed** |  |





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

### getSocketIds



```php
public getSocketIds(): array
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
> Automatically generated on 2025-07-24
