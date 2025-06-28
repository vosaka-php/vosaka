***

# FileIOException

Exception thrown when file input/output operations fail.

This exception is thrown when file I/O operations such as reading, writing,
copying, or moving files fail due to system-level issues, disk problems,
or other I/O related errors.

* Full name: `\venndev\vosaka\fs\exceptions\FileIOException`
* Parent class: [`\venndev\vosaka\fs\exceptions\FileSystemException`](./FileSystemException.md)



## Properties


### ioOperation

The type of I/O operation that failed.

```php
protected string $ioOperation
```






***

### bytesInvolved

The number of bytes involved in the operation (if applicable).

```php
protected ?int $bytesInvolved
```






***

### systemErrorCode

System error code (if available).

```php
protected ?int $systemErrorCode
```






***

## Methods


### __construct

FileIOException constructor.

```php
public __construct(string $path, string $ioOperation, string $operation = &quot;file operation&quot;, int|null $bytesInvolved = null, int|null $systemErrorCode = null, array $context = [], int $code, \Exception|null $previous = null): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The path where the I/O operation failed |
| `$ioOperation` | **string** | The specific I/O operation that failed |
| `$operation` | **string** | The higher-level operation being performed |
| `$bytesInvolved` | **int&#124;null** | Number of bytes involved in the operation |
| `$systemErrorCode` | **int&#124;null** | System error code |
| `$context` | **array** | Additional context information |
| `$code` | **int** | The exception code |
| `$previous` | **\Exception&#124;null** | The previous exception |





***

### getIOOperation

Get the I/O operation that failed.

```php
public getIOOperation(): string
```









**Return Value:**

The I/O operation




***

### getBytesInvolved

Get the number of bytes involved in the operation.

```php
public getBytesInvolved(): int|null
```









**Return Value:**

The number of bytes




***

### getSystemErrorCode

Get the system error code.

```php
public getSystemErrorCode(): int|null
```









**Return Value:**

The system error code




***

### forRead

Create a FileIOException for read operations.

```php
public static forRead(string $path, int|null $bytesAttempted = null, array $context = []): static
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The file path |
| `$bytesAttempted` | **int&#124;null** | Number of bytes attempted to read |
| `$context` | **array** | Additional context |





***

### forWrite

Create a FileIOException for write operations.

```php
public static forWrite(string $path, int|null $bytesAttempted = null, array $context = []): static
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The file path |
| `$bytesAttempted` | **int&#124;null** | Number of bytes attempted to write |
| `$context` | **array** | Additional context |





***

### forCopy

Create a FileIOException for copy operations.

```php
public static forCopy(string $sourcePath, string $destinationPath, array $context = []): static
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$sourcePath` | **string** | The source file path |
| `$destinationPath` | **string** | The destination file path |
| `$context` | **array** | Additional context |





***

### forMove

Create a FileIOException for move/rename operations.

```php
public static forMove(string $sourcePath, string $destinationPath, array $context = []): static
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$sourcePath` | **string** | The source file path |
| `$destinationPath` | **string** | The destination file path |
| `$context` | **array** | Additional context |





***

### forDelete

Create a FileIOException for delete operations.

```php
public static forDelete(string $path, array $context = []): static
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The file path |
| `$context` | **array** | Additional context |





***

### forOpen

Create a FileIOException for file open operations.

```php
public static forOpen(string $path, string $mode = &quot;r&quot;, array $context = []): static
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The file path |
| `$mode` | **string** | The file open mode |
| `$context` | **array** | Additional context |





***

### forClose

Create a FileIOException for file close operations.

```php
public static forClose(string $path, array $context = []): static
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The file path |
| `$context` | **array** | Additional context |





***

### forFlush

Create a FileIOException for flush operations.

```php
public static forFlush(string $path, array $context = []): static
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The file path |
| `$context` | **array** | Additional context |





***

### forSync

Create a FileIOException for sync operations.

```php
public static forSync(string $path, array $context = []): static
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The file path |
| `$context` | **array** | Additional context |





***

### withSystemError

Create a FileIOException with system error information.

```php
public static withSystemError(string $path, string $ioOperation, string $operation, int $systemErrorCode, array $context = []): static
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The file path |
| `$ioOperation` | **string** | The I/O operation |
| `$operation` | **string** | The higher-level operation |
| `$systemErrorCode` | **int** | System error code |
| `$context` | **array** | Additional context |





***

### withMessage

Create a FileIOException with custom message.

```php
public static withMessage(string $message, string $path, string $ioOperation, string $operation = &quot;file operation&quot;, array $context = []): static
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$message` | **string** | Custom error message |
| `$path` | **string** | The file path |
| `$ioOperation` | **string** | The I/O operation |
| `$operation` | **string** | The higher-level operation |
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
