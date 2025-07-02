***

# FileSystemException

Base exception class for all file system related exceptions.

This class serves as the parent for all file system exceptions in the VOsaka framework.
It provides common functionality and allows for easy exception handling by catching
this base class to handle all file system related errors.

* Full name: `\venndev\vosaka\fs\exceptions\FileSystemException`
* Parent class: [`Exception`](../../../../Exception.md)



## Properties


### path

The path that caused the exception.

```php
protected string $path
```






***

### operation

The operation that was being performed when the exception occurred.

```php
protected string $operation
```






***

### context

Additional context information about the exception.

```php
protected array $context
```






***

## Methods


### __construct

FileSystemException constructor.

```php
public __construct(string $message, string $path = &#039;&#039;, string $operation = &#039;&#039;, array $context = [], int $code, \Exception|null $previous = null): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$message` | **string** | The exception message |
| `$path` | **string** | The path that caused the exception |
| `$operation` | **string** | The operation being performed |
| `$context` | **array** | Additional context information |
| `$code` | **int** | The exception code |
| `$previous` | **\Exception&#124;null** | The previous exception |





***

### getPath

Get the path that caused the exception.

```php
public getPath(): string
```









**Return Value:**

The path




***

### getOperation

Get the operation that was being performed.

```php
public getOperation(): string
```









**Return Value:**

The operation




***

### getContext

Get additional context information.

```php
public getContext(): array
```









**Return Value:**

The context




***

### getFormattedMessage

Get a formatted string representation of the exception.

```php
public getFormattedMessage(): string
```









**Return Value:**

The formatted exception string




***


***
> Automatically generated on 2025-07-02
