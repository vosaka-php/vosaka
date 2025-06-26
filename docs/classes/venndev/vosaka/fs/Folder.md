***

# Folder





* Full name: `\venndev\vosaka\fs\Folder`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### operationLocks



```php
private static array $operationLocks
```



* This property is **static**.


***

### lockDir



```php
private static string $lockDir
```



* This property is **static**.


***

### isShutdownHandlerRegistered



```php
private static bool $isShutdownHandlerRegistered
```



* This property is **static**.


***

## Methods


### registerShutdownHandler



```php
private static registerShutdownHandler(): void
```



* This method is **static**.








***

### initLockDir



```php
private static initLockDir(): void
```



* This method is **static**.








***

### acquireLock



```php
private static acquireLock(string $operation, string $path): string
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$operation` | **string** |  |
| `$path` | **string** |  |





***

### releaseLock



```php
private static releaseLock(string $lockFile): void
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$lockFile` | **string** |  |





***

### createBackup



```php
private static createBackup(string $path): ?string
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** |  |





***

### recursiveDelete



```php
private static recursiveDelete(string $path): void
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** |  |





***

### restoreFromBackup



```php
private static restoreFromBackup(string $originalPath, string $backupPath): bool
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$originalPath` | **string** |  |
| `$backupPath` | **string** |  |





***

### validatePath



```php
private static validatePath(string $path): void
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** |  |





***

### createTempFile



```php
private static createTempFile(string $destinationPath): string
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$destinationPath` | **string** |  |





***

### copy

Copies a directory from source to destination.

```php
public static copy(string $source, string $destination): \venndev\vosaka\core\Result&lt;bool&gt;
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$source` | **string** | The source directory path. |
| `$destination` | **string** | The destination directory path. |


**Return Value:**

Returns true if the copy was successful, false otherwise.



**Throws:**
<p>If the source or destination paths are invalid.</p>

- [`InvalidArgumentException`](../../../InvalidArgumentException.md)
<p>If the copy operation fails.</p>

- [`RuntimeException`](../../../RuntimeException.md)



***

### delete

Deletes a directory and its contents.

```php
public static delete(string $path): \venndev\vosaka\core\Result&lt;bool&gt;
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The path to the directory to delete. |


**Return Value:**

Returns true if the deletion was successful, false otherwise.



**Throws:**
<p>If the path is invalid or not a directory.</p>

- [`InvalidArgumentException`](../../../InvalidArgumentException.md)
<p>If the deletion operation fails.</p>

- [`RuntimeException`](../../../RuntimeException.md)



***

### move

Moves a directory from source to destination.

```php
public static move(string $source, string $destination): \venndev\vosaka\core\Result&lt;bool&gt;
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$source` | **string** | The source directory path. |
| `$destination` | **string** | The destination directory path. |


**Return Value:**

Returns true if the move was successful, false otherwise.



**Throws:**
<p>If the source or destination paths are invalid.</p>

- [`InvalidArgumentException`](../../../InvalidArgumentException.md)
<p>If the move operation fails.</p>

- [`RuntimeException`](../../../RuntimeException.md)



***

### exists



```php
public static exists(string $path): bool
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** |  |





***

### create

Creates a new directory at the specified path with the given permissions.

```php
public static create(string $path, int $permissions = 0755): \venndev\vosaka\core\Result&lt;bool&gt;
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The path to create the directory at. |
| `$permissions` | **int** | The permissions to set for the new directory (default is 0755). |


**Return Value:**

Returns true if the directory was created successfully, false otherwise.



**Throws:**
<p>If the path is invalid.</p>

- [`InvalidArgumentException`](../../../InvalidArgumentException.md)
<p>If the directory cannot be created.</p>

- [`RuntimeException`](../../../RuntimeException.md)



***

### size

Returns the size of a directory in bytes.

```php
public static size(string $path): \venndev\vosaka\core\Result&lt;int&gt;
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The path to the directory. |


**Return Value:**

Returns the size of the directory in bytes.




***

### list

Lists the contents of a directory.

```php
public static list(string $path, bool $recursive = false): \venndev\vosaka\core\Result&lt;array&gt;
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The path to the directory. |
| `$recursive` | **bool** | Whether to list contents recursively (default is false). |


**Return Value:**

Returns an array of items in the directory.




***

### cleanup

Cleans up temporary files and locks created by Folder operations.

```php
public static cleanup(): void
```

This method should be called during graceful shutdown.

* This method is **static**.








***

### forceCleanup

Forcefully cleans up all temporary files and locks, ignoring graceful shutdown state.

```php
public static forceCleanup(): void
```

This should be used in emergency situations where graceful shutdown is not possible.

* This method is **static**.








***

### finalCleanup

Final cleanup method to be called on script termination.

```php
public static finalCleanup(): void
```

It releases all locks and cleans up temporary files.

* This method is **static**.








***


***
> Automatically generated on 2025-06-26
