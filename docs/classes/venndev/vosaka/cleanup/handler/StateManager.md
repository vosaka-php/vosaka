***

# StateManager

Handles state persistence



* Full name: `\venndev\vosaka\cleanup\handler\StateManager`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### stateFile



```php
private string $stateFile
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
public __construct(string $stateFile, \venndev\vosaka\cleanup\logger\LoggerInterface $logger): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$stateFile` | **string** |  |
| `$logger` | **\venndev\vosaka\cleanup\logger\LoggerInterface** |  |





***

### saveState



```php
public saveState(array $state): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$state` | **array** |  |





***

### loadState



```php
public loadState(): ?array
```












***

### cleanupPreviousState



```php
public cleanupPreviousState(): void
```












***

### removeStateFile



```php
public removeStateFile(): void
```












***

### setStateFile



```php
public setStateFile(string $stateFile): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$stateFile` | **string** |  |





***

### logPreviousResources



```php
private logPreviousResources(string $type, array $state): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$type` | **string** |  |
| `$state` | **array** |  |





***


***
> Automatically generated on 2025-07-24
