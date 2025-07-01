***

# File

File class for asynchronous file operations.

Provides asynchronous file I/O operations that work with the VOsaka event loop.
All operations are designed to be non-blocking and yield control appropriately
to allow other tasks to execute while file operations are in progress.

The class uses chunked reading for large files and atomic write operations
with temporary files to ensure data integrity.

* Full name: `\venndev\vosaka\fs\File`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**




## Methods


### read

Asynchronously read a file in chunks.

```php
public static read(string $path): \Generator&lt;string&gt;
```

Reads the specified file in 8KB chunks, yielding each chunk as it's read.
This approach prevents memory exhaustion when reading large files and
allows other tasks to execute between chunks. The file is automatically
closed after reading completes or if an error occurs.

* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The path to the file to read |


**Return Value:**

Yields string chunks of the file content



**Throws:**
<p>If the file does not exist</p>

- [`InvalidArgumentException`](./InvalidArgumentException.md)
<p>If the file cannot be opened or read</p>

- [`RuntimeException`](./RuntimeException.md)



***

### write

Asynchronously write data to a file with atomic operations.

```php
public static write(string $path, string $data): \Generator&lt;int&gt;
```

Writes data to a file using a temporary file approach to ensure atomicity.
The data is first written to a temporary file, then atomically renamed to
the target path. This prevents data corruption if the write operation is
interrupted. The temporary file is registered with the graceful shutdown
manager for cleanup.

The operation includes proper file synchronization (fsync) to ensure data
is written to disk before the operation completes.

* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | The path where the file should be written |
| `$data` | **string** | The data to write to the file |


**Return Value:**

Yields the number of bytes written



**Throws:**
<p>If the file cannot be opened, written to, or renamed</p>

- [`RuntimeException`](./RuntimeException.md)



***


***
> Automatically generated on 2025-07-01
