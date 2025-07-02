***

# Folder

Provides comprehensive directory manipulation functions with async/await patterns,
proper resource management, and graceful shutdown integration. All operations
that involve streams, temporary files, or long-running processes use GracefulShutdown
for proper cleanup.



* Full name: `\venndev\vosaka\fs\Folder`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**


## Constants

| Constant | Visibility | Type | Value |
|:---------|:-----------|:-----|:------|
|`DEFAULT_CHUNK_SIZE`|private| |8192|
|`DEFAULT_PERMISSIONS`|private| |0755|
|`LOCK_TIMEOUT_SECONDS`|private| |30.0|


## Methods


### createDir

Asynchronously create a directory with all parent directories.

```php
public static createDir(string $path, int $permissions = self::DEFAULT_PERMISSIONS, bool $recursive = true): \venndev\vosaka\core\Result&lt;bool&gt;
```

This function creates the specified
directory and all necessary parent directories. Operations are chunked to
allow other tasks to execute.

* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The directory path to create |
| `$permissions` | **int** | The permissions to set (default: 0755) |
| `$recursive` | **bool** | Whether to create parent directories (default: true) |


**Return Value:**

Returns true on success



**Throws:**
<p>If directory creation fails</p>

- [`DirectoryException`](./exceptions/DirectoryException.md)



***

### removeDir

Asynchronously remove a directory and its contents.

```php
public static removeDir(string $path, bool $recursive = true): \venndev\vosaka\core\Result&lt;bool&gt;
```

Recursively removes the directory
and all its contents. Uses GracefulShutdown to track temporary operations.

* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The directory path to remove |
| `$recursive` | **bool** | Whether to remove contents recursively (default: true) |


**Return Value:**

Returns true on success



**Throws:**
<p>If directory removal fails</p>

- [`DirectoryException`](./exceptions/DirectoryException.md)



***

### readDir

Asynchronously read directory entries.

```php
public static readDir(string $path, bool $includeHidden = false): \venndev\vosaka\core\Result&lt;\SplFileInfo&gt;
```

Returns an async iterator over directory entries.
Yields each entry to allow for non-blocking iteration over large directories.

* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The directory path to read |
| `$includeHidden` | **bool** | Whether to include hidden files (default: false) |


**Return Value:**

Yields SplFileInfo objects for each entry



**Throws:**
<p>If directory cannot be read</p>

- [`DirectoryException`](./exceptions/DirectoryException.md)



***

### walkDir

Asynchronously walk directory tree recursively.

```php
public static walkDir(string $path, int $maxDepth = -1, callable|null $filter = null): \venndev\vosaka\core\Result&lt;\SplFileInfo&gt;
```

Recursively traverses the directory
tree and yields each file/directory encountered. Supports filtering and
depth limits.

* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The root directory to walk |
| `$maxDepth` | **int** | Maximum depth to traverse (-1 for unlimited) |
| `$filter` | **callable&#124;null** | Optional filter function for entries |


**Return Value:**

Yields SplFileInfo objects for each entry



**Throws:**
<p>If directory cannot be walked</p>

- [`DirectoryException`](./exceptions/DirectoryException.md)



***

### copyDir

Asynchronously copy directory and its contents.

```php
public static copyDir(string $source, string $destination, bool $overwrite = false): \venndev\vosaka\core\Result&lt;int&gt;
```

Copies the entire directory
tree from source to destination. Uses temporary files and GracefulShutdown
for proper resource management.

* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$source` | **string** | Source directory path |
| `$destination` | **string** | Destination directory path |
| `$overwrite` | **bool** | Whether to overwrite existing files (default: false) |


**Return Value:**

Returns number of files copied



**Throws:**
<p>If copy operation fails</p>

- [`DirectoryException`](./exceptions/DirectoryException.md)



***

### moveDir

Asynchronously move/rename directory.

```php
public static moveDir(string $source, string $destination): \venndev\vosaka\core\Result&lt;bool&gt;
```

Moves or renames a directory atomically
when possible, or falls back to copy+delete for cross-filesystem moves.

* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$source` | **string** | Source directory path |
| `$destination` | **string** | Destination directory path |


**Return Value:**

Returns true on success



**Throws:**
<p>If move operation fails</p>

- [`DirectoryException`](./exceptions/DirectoryException.md)



***

### watchDir

Asynchronously watch directory for changes.

```php
public static watchDir(string $path, float $pollInterval = 1.0, callable|null $filter = null): \venndev\vosaka\core\Result&lt;array&gt;
```

Monitors directory for changes
and yields events. Uses polling with configurable intervals.

* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | Directory path to watch |
| `$pollInterval` | **float** | Polling interval in seconds (default: 1.0) |
| `$filter` | **callable&#124;null** | Optional filter for change events |


**Return Value:**

Yields change events as arrays



**Throws:**
<p>If directory cannot be watched</p>

- [`DirectoryException`](./exceptions/DirectoryException.md)



***

### createTempDir

Asynchronously create temporary directory.

```php
public static createTempDir(string|null $prefix = null, string|null $tempDir = null): \venndev\vosaka\core\Result&lt;string&gt;
```

Creates a temporary directory with unique name and registers it with
GracefulShutdown for automatic cleanup.

* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$prefix` | **string&#124;null** | Optional prefix for directory name |
| `$tempDir` | **string&#124;null** | Base temporary directory (default: system temp) |


**Return Value:**

Returns path to created temporary directory



**Throws:**
<p>If temporary directory creation fails</p>

- [`DirectoryException`](./exceptions/DirectoryException.md)



***

### lockDir

Asynchronously lock directory for exclusive access.

```php
public static lockDir(string $path, float $timeout = self::LOCK_TIMEOUT_SECONDS): \venndev\vosaka\core\Result&lt;resource&gt;
```

Creates a lock file in the directory to prevent concurrent access.
Uses GracefulShutdown to ensure lock cleanup.

* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | Directory path to lock |
| `$timeout` | **float** | Timeout in seconds for acquiring lock |


**Return Value:**

Returns lock file handle



**Throws:**
<p>If lock cannot be acquired</p>

- [`LockException`](./exceptions/LockException.md)



***

### unlockDir

Release directory lock.

```php
public static unlockDir(resource $lockHandle, string $path): \venndev\vosaka\core\Result&lt;bool&gt;
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$lockHandle` | **resource** | Lock handle returned by lockDir() |
| `$path` | **string** | Directory path that was locked |


**Return Value:**

Returns true on success




***

### metadata

Get directory metadata asynchronously.

```php
public static metadata(string $path): \venndev\vosaka\core\Result&lt;array&gt;
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | Directory path |


**Return Value:**

Returns directory metadata



**Throws:**
<p>If directory cannot be accessed</p>

- [`DirectoryException`](./exceptions/DirectoryException.md)



***

### removeDirSync

Synchronous helper method for cleanup operations.

```php
private static removeDirSync(string $path): bool
```

Used internally by GracefulShutdown callbacks.

* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** |  |





***

### calculateSize

Asynchronously calculate directory size.

```php
public static calculateSize(string $path): \venndev\vosaka\core\Result&lt;int&gt;
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | Directory path |


**Return Value:**

Returns total size in bytes



**Throws:**
<p>If directory cannot be accessed</p>

- [`DirectoryException`](./exceptions/DirectoryException.md)



***

### find

Asynchronously find files matching pattern.

```php
public static find(string $path, string $pattern, bool $recursive = true): \venndev\vosaka\core\Result&lt;\SplFileInfo&gt;
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | Directory path to search in |
| `$pattern` | **string** | Glob pattern to match |
| `$recursive` | **bool** | Whether to search recursively |


**Return Value:**

Yields matching files



**Throws:**
<p>If directory cannot be searched</p>

- [`DirectoryException`](./exceptions/DirectoryException.md)



***


***
> Automatically generated on 2025-07-02
