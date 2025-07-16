***

# FileNotFoundException

Exception thrown when a file or directory is not found.

This exception is thrown when attempting to perform operations on files
or directories that do not exist on the file system.

* Full name: `\venndev\vosaka\fs\exceptions\FileNotFoundException`
* Parent class: [`\venndev\vosaka\fs\exceptions\FileSystemException`](./FileSystemException.md)




## Methods


### __construct

FileNotFoundException constructor.

```php
public __construct(string $path, string $operation = &quot;access&quot;, array $context = [], int $code, \Exception|null $previous = null): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The path that was not found |
| `$operation` | **string** | The operation being performed |
| `$context` | **array** | Additional context information |
| `$code` | **int** | The exception code |
| `$previous` | **\Exception&#124;null** | The previous exception |





***

### forFile

Create a FileNotFoundException for a file.

```php
public static forFile(string $path, string $operation = &quot;read&quot;, array $context = []): static
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The file path |
| `$operation` | **string** | The operation being performed |
| `$context` | **array** | Additional context |





***

### forDirectory

Create a FileNotFoundException for a directory.

```php
public static forDirectory(string $path, string $operation = &quot;access&quot;, array $context = []): static
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The directory path |
| `$operation` | **string** | The operation being performed |
| `$context` | **array** | Additional context |





***

### withMessage

Create a FileNotFoundException with a custom message.

```php
public static withMessage(string $message, string $path, string $operation = &quot;access&quot;, array $context = []): static
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$message` | **string** | Custom error message |
| `$path` | **string** | The path that was not found |
| `$operation` | **string** | The operation being performed |
| `$context` | **array** | Additional context |





***


## Inherited methods


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
> Automatically generated on 2025-07-16
