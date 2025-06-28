***

# FilePermissionException

Exception thrown when a file system operation fails due to permission issues.

This exception is thrown when attempting to perform operations that require
specific permissions (read, write, execute) on files or directories.

* Full name: `\venndev\vosaka\fs\exceptions\FilePermissionException`
* Parent class: [`\venndev\vosaka\fs\exceptions\FileSystemException`](./FileSystemException.md)



## Properties


### requiredPermission

The required permission that was missing.

```php
protected string $requiredPermission
```






***

### currentPermissions

The current permissions of the file/directory.

```php
protected ?string $currentPermissions
```






***

## Methods


### __construct

FilePermissionException constructor.

```php
public __construct(string $path, string $requiredPermission, string $operation = &quot;access&quot;, string|null $currentPermissions = null, array $context = [], int $code, \Exception|null $previous = null): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The path with permission issues |
| `$requiredPermission` | **string** | The required permission (read, write, execute) |
| `$operation` | **string** | The operation being performed |
| `$currentPermissions` | **string&#124;null** | The current permissions (octal format) |
| `$context` | **array** | Additional context information |
| `$code` | **int** | The exception code |
| `$previous` | **\Exception&#124;null** | The previous exception |





***

### getRequiredPermission

Get the required permission that was missing.

```php
public getRequiredPermission(): string
```









**Return Value:**

The required permission




***

### getCurrentPermissions

Get the current permissions of the file/directory.

```php
public getCurrentPermissions(): string|null
```









**Return Value:**

The current permissions in octal format




***

### forRead

Create a FilePermissionException for read permission.

```php
public static forRead(string $path, string $operation = &quot;read&quot;, array $context = []): static
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The file path |
| `$operation` | **string** | The operation being performed |
| `$context` | **array** | Additional context |





***

### forWrite

Create a FilePermissionException for write permission.

```php
public static forWrite(string $path, string $operation = &quot;write&quot;, array $context = []): static
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The file path |
| `$operation` | **string** | The operation being performed |
| `$context` | **array** | Additional context |





***

### forExecute

Create a FilePermissionException for execute permission.

```php
public static forExecute(string $path, string $operation = &quot;execute&quot;, array $context = []): static
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The file path |
| `$operation` | **string** | The operation being performed |
| `$context` | **array** | Additional context |





***

### forDirectoryCreation

Create a FilePermissionException for directory creation.

```php
public static forDirectoryCreation(string $path, array $context = []): static
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The directory path |
| `$context` | **array** | Additional context |





***

### withMessage

Create a FilePermissionException with custom message.

```php
public static withMessage(string $message, string $path, string $requiredPermission, string $operation = &quot;access&quot;, array $context = []): static
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$message` | **string** | Custom error message |
| `$path` | **string** | The file path |
| `$requiredPermission` | **string** | The required permission |
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
> Automatically generated on 2025-06-28
