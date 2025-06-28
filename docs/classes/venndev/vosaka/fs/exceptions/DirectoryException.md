***

# DirectoryException

Exception thrown when directory-specific operations fail.

This exception is thrown when operations specific to directories
fail, such as creating, deleting, listing, or managing directory
contents and permissions.

* Full name: `\venndev\vosaka\fs\exceptions\DirectoryException`
* Parent class: [`\venndev\vosaka\fs\exceptions\FileSystemException`](./FileSystemException.md)



## Properties


### directoryOperation

The type of directory operation that failed.

```php
protected string $directoryOperation
```






***

### directoryMode

The directory mode/permissions involved (if applicable).

```php
protected ?int $directoryMode
```






***

### isRecursive

Whether the operation was recursive.

```php
protected bool $isRecursive
```






***

### itemCount

The number of items involved in the operation (if applicable).

```php
protected ?int $itemCount
```






***

## Methods


### __construct

DirectoryException constructor.

```php
public __construct(string $path, string $directoryOperation, string $operation = &quot;directory operation&quot;, int|null $directoryMode = null, bool $isRecursive = false, int|null $itemCount = null, array $context = [], int $code, \Exception|null $previous = null): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The directory path where the operation failed |
| `$directoryOperation` | **string** | The specific directory operation that failed |
| `$operation` | **string** | The higher-level operation being performed |
| `$directoryMode` | **int&#124;null** | The directory mode/permissions |
| `$isRecursive` | **bool** | Whether the operation was recursive |
| `$itemCount` | **int&#124;null** | Number of items involved |
| `$context` | **array** | Additional context information |
| `$code` | **int** | The exception code |
| `$previous` | **\Exception&#124;null** | The previous exception |





***

### getDirectoryOperation

Get the directory operation that failed.

```php
public getDirectoryOperation(): string
```









**Return Value:**

The directory operation




***

### getDirectoryMode

Get the directory mode/permissions.

```php
public getDirectoryMode(): int|null
```









**Return Value:**

The directory mode




***

### isRecursive

Check if the operation was recursive.

```php
public isRecursive(): bool
```









**Return Value:**

True if recursive




***

### getItemCount

Get the number of items involved in the operation.

```php
public getItemCount(): int|null
```









**Return Value:**

The item count




***

### forCreate

Create a DirectoryException for create operations.

```php
public static forCreate(string $path, int $mode = 0755, bool $recursive = false, array $context = []): static
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The directory path |
| `$mode` | **int** | The directory permissions |
| `$recursive` | **bool** | Whether the operation was recursive |
| `$context` | **array** | Additional context |





***

### forDelete

Create a DirectoryException for delete operations.

```php
public static forDelete(string $path, bool $recursive = false, int|null $itemCount = null, array $context = []): static
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The directory path |
| `$recursive` | **bool** | Whether the operation was recursive |
| `$itemCount` | **int&#124;null** | Number of items in the directory |
| `$context` | **array** | Additional context |





***

### forList

Create a DirectoryException for list operations.

```php
public static forList(string $path, bool $recursive = false, array $context = []): static
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The directory path |
| `$recursive` | **bool** | Whether the operation was recursive |
| `$context` | **array** | Additional context |





***

### forCopy

Create a DirectoryException for copy operations.

```php
public static forCopy(string $sourcePath, string $destinationPath, bool $recursive = true, int|null $itemCount = null, array $context = []): static
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$sourcePath` | **string** | The source directory path |
| `$destinationPath` | **string** | The destination directory path |
| `$recursive` | **bool** | Whether the operation was recursive |
| `$itemCount` | **int&#124;null** | Number of items to copy |
| `$context` | **array** | Additional context |





***

### forMove

Create a DirectoryException for move operations.

```php
public static forMove(string $sourcePath, string $destinationPath, int|null $itemCount = null, array $context = []): static
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$sourcePath` | **string** | The source directory path |
| `$destinationPath` | **string** | The destination directory path |
| `$itemCount` | **int&#124;null** | Number of items to move |
| `$context` | **array** | Additional context |





***

### forEmpty

Create a DirectoryException for empty directory operations.

```php
public static forEmpty(string $path, int|null $itemCount = null, array $context = []): static
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The directory path |
| `$itemCount` | **int&#124;null** | Number of items that prevented the operation |
| `$context` | **array** | Additional context |





***

### forRead

Create a DirectoryException for read operations.

```php
public static forRead(string $path, array $context = []): static
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The directory path |
| `$context` | **array** | Additional context |





***

### forTraversal

Create a DirectoryException for traversal operations.

```php
public static forTraversal(string $path, bool $recursive = false, int|null $depthReached = null, array $context = []): static
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The directory path |
| `$recursive` | **bool** | Whether the traversal was recursive |
| `$depthReached` | **int&#124;null** | The depth reached before failure |
| `$context` | **array** | Additional context |





***

### forSize

Create a DirectoryException for size calculation operations.

```php
public static forSize(string $path, bool $recursive = true, int|null $itemsProcessed = null, array $context = []): static
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The directory path |
| `$recursive` | **bool** | Whether the calculation was recursive |
| `$itemsProcessed` | **int&#124;null** | Number of items processed before failure |
| `$context` | **array** | Additional context |





***

### forNotEmpty

Create a DirectoryException for not empty situations.

```php
public static forNotEmpty(string $path, int $itemCount, string $operation = &quot;directory operation&quot;, array $context = []): static
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The directory path |
| `$itemCount` | **int** | Number of items in the directory |
| `$operation` | **string** | The operation that requires empty directory |
| `$context` | **array** | Additional context |





***

### forAlreadyExists

Create a DirectoryException for already exists situations.

```php
public static forAlreadyExists(string $path, string $operation = &quot;create directory&quot;, array $context = []): static
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

Create a DirectoryException with custom message.

```php
public static withMessage(string $message, string $path, string $directoryOperation, string $operation = &quot;directory operation&quot;, array $context = []): static
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$message` | **string** | Custom error message |
| `$path` | **string** | The directory path |
| `$directoryOperation` | **string** | The directory operation |
| `$operation` | **string** | The higher-level operation |
| `$context` | **array** | Additional context |





***

### forCreation

Create a DirectoryException for creation operations.

```php
public static forCreation(string $path, string $message = &quot;&quot;, array $context = []): static
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The directory path |
| `$message` | **string** | Additional error message |
| `$context` | **array** | Additional context |





***

### forRemoval

Create a DirectoryException for removal operations.

```php
public static forRemoval(string $path, string $message = &quot;&quot;, array $context = []): static
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The directory path |
| `$message` | **string** | Additional error message |
| `$context` | **array** | Additional context |





***

### forNotFound

Create a DirectoryException for not found situations.

```php
public static forNotFound(string $path, array $context = []): static
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The directory path |
| `$context` | **array** | Additional context |





***

### forPermission

Create a DirectoryException for permission operations.

```php
public static forPermission(string $path, string $operation = &quot;access&quot;, array $context = []): static
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The directory path |
| `$operation` | **string** | The operation requiring permission |
| `$context` | **array** | Additional context |





***

### forWalk

Create a DirectoryException for walk operations.

```php
public static forWalk(string $path, string $message = &quot;&quot;, array $context = []): static
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The directory path |
| `$message` | **string** | Additional error message |
| `$context` | **array** | Additional context |





***

### forExists

Create a DirectoryException for exists situations.

```php
public static forExists(string $path, array $context = []): static
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The directory path |
| `$context` | **array** | Additional context |





***

### forStat

Create a DirectoryException for stat operations.

```php
public static forStat(string $path, array $context = []): static
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The directory path |
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
