***

# InvalidPathException

Exception thrown when a file path is invalid or malformed.

This exception is thrown when attempting to use file paths that are
invalid, malformed, contain illegal characters, or point to restricted
system locations that should not be accessed.

* Full name: `\venndev\vosaka\fs\exceptions\InvalidPathException`
* Parent class: [`\venndev\vosaka\fs\exceptions\FileSystemException`](./FileSystemException.md)



## Properties


### validationType

The type of path validation that failed.

```php
protected string $validationType
```






***

### expectedFormat

The expected path format or pattern.

```php
protected ?string $expectedFormat
```






***

## Methods


### __construct

InvalidPathException constructor.

```php
public __construct(string $path, string $validationType, string $operation = &quot;path validation&quot;, string|null $expectedFormat = null, array $context = [], int $code, \Exception|null $previous = null): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The invalid path |
| `$validationType` | **string** | The type of validation that failed |
| `$operation` | **string** | The operation being performed |
| `$expectedFormat` | **string&#124;null** | The expected path format |
| `$context` | **array** | Additional context information |
| `$code` | **int** | The exception code |
| `$previous` | **\Exception&#124;null** | The previous exception |





***

### getValidationType

Get the type of path validation that failed.

```php
public getValidationType(): string
```









**Return Value:**

The validation type




***

### getExpectedFormat

Get the expected path format.

```php
public getExpectedFormat(): string|null
```









**Return Value:**

The expected format




***

### forEmptyPath

Create an InvalidPathException for empty paths.

```php
public static forEmptyPath(string $operation = &quot;access&quot;, array $context = []): static
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$operation` | **string** | The operation being performed |
| `$context` | **array** | Additional context |





***

### forNullByte

Create an InvalidPathException for null byte attacks.

```php
public static forNullByte(string $path, string $operation = &quot;access&quot;, array $context = []): static
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The path containing null bytes |
| `$operation` | **string** | The operation being performed |
| `$context` | **array** | Additional context |





***

### forTooLong

Create an InvalidPathException for paths that are too long.

```php
public static forTooLong(string $path, int $maxLength, string $operation = &quot;access&quot;, array $context = []): static
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The path that is too long |
| `$maxLength` | **int** | The maximum allowed length |
| `$operation` | **string** | The operation being performed |
| `$context` | **array** | Additional context |





***

### forIllegalCharacters

Create an InvalidPathException for illegal characters.

```php
public static forIllegalCharacters(string $path, array $illegalChars, string $operation = &quot;access&quot;, array $context = []): static
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The path with illegal characters |
| `$illegalChars` | **array** | The illegal characters found |
| `$operation` | **string** | The operation being performed |
| `$context` | **array** | Additional context |





***

### forRestrictedPath

Create an InvalidPathException for restricted system paths.

```php
public static forRestrictedPath(string $path, array $restrictedPaths = [], string $operation = &quot;access&quot;, array $context = []): static
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The restricted path |
| `$restrictedPaths` | **array** | List of restricted path patterns |
| `$operation` | **string** | The operation being performed |
| `$context` | **array** | Additional context |





***

### forOutsideAllowedDirectory

Create an InvalidPathException for paths outside allowed directories.

```php
public static forOutsideAllowedDirectory(string $path, array $allowedDirectories, string $operation = &quot;access&quot;, array $context = []): static
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The path outside allowed directories |
| `$allowedDirectories` | **array** | List of allowed directory patterns |
| `$operation` | **string** | The operation being performed |
| `$context` | **array** | Additional context |





***

### forPathTraversal

Create an InvalidPathException for path traversal attempts.

```php
public static forPathTraversal(string $path, string $operation = &quot;access&quot;, array $context = []): static
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The path with traversal attempts |
| `$operation` | **string** | The operation being performed |
| `$context` | **array** | Additional context |





***

### forMalformedPath

Create an InvalidPathException for malformed paths.

```php
public static forMalformedPath(string $path, string $reason, string $operation = &quot;access&quot;, array $context = []): static
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The malformed path |
| `$reason` | **string** | The reason why the path is malformed |
| `$operation` | **string** | The operation being performed |
| `$context` | **array** | Additional context |





***

### forUnsupportedType

Create an InvalidPathException for unsupported path types.

```php
public static forUnsupportedType(string $path, string $pathType, array $supportedTypes, string $operation = &quot;access&quot;, array $context = []): static
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The unsupported path |
| `$pathType` | **string** | The detected path type |
| `$supportedTypes` | **array** | List of supported path types |
| `$operation` | **string** | The operation being performed |
| `$context` | **array** | Additional context |





***

### withMessage

Create an InvalidPathException with custom message.

```php
public static withMessage(string $message, string $path, string $validationType, string $operation = &quot;path validation&quot;, array $context = []): static
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$message` | **string** | Custom error message |
| `$path` | **string** | The invalid path |
| `$validationType` | **string** | The validation type |
| `$operation` | **string** | The operation being performed |
| `$context` | **array** | Additional context |





***

### forPath

Create an InvalidPathException for a general path with custom reason.

```php
public static forPath(string $path, string $reason, array $context = []): static
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The invalid path |
| `$reason` | **string** | The reason why the path is invalid |
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
