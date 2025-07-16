***

# TempFileHandler

Handles temporary file cleanup



* Full name: `\venndev\vosaka\cleanup\handler\TempFileHandler`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### tempFiles



```php
private array $tempFiles
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

### addTempFile



```php
public addTempFile(string $filePath): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$filePath` | **string** |  |





***

### removeTempFile



```php
public removeTempFile(string $path): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** |  |





***

### cleanupAll



```php
public cleanupAll(): void
```












***

### getTempFiles



```php
public getTempFiles(): array
```












***

### getCount



```php
public getCount(): int
```












***


***
> Automatically generated on 2025-07-16
