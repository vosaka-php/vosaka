***

# FileLogger

Simple file logger implementation



* Full name: `\venndev\vosaka\cleanup\logger\FileLogger`
* This class is marked as **final** and can't be subclassed
* This class implements:
[`\venndev\vosaka\cleanup\logger\LoggerInterface`](./LoggerInterface.md)
* This class is a **Final class**



## Properties


### enableLogging



```php
private bool $enableLogging
```






***

### logFile



```php
private string $logFile
```






***

## Methods


### __construct



```php
public __construct(string $logFile = &#039;/tmp/graceful_shutdown.log&#039;, bool $enableLogging = false): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$logFile` | **string** |  |
| `$enableLogging` | **bool** |  |





***

### log



```php
public log(string $message): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$message` | **string** |  |





***

### setLogging



```php
public setLogging(bool $enableLogging): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$enableLogging` | **bool** |  |





***

### setLogFile



```php
public setLogFile(string $logFile): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$logFile` | **string** |  |





***


***
> Automatically generated on 2025-07-04
