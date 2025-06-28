# Folder Operations

This document describes the comprehensive set of asynchronous folder operations in VOsaka filesystem module. These operations provide non-blocking, async/await style directory manipulation with proper resource management and graceful shutdown integration.

## Overview
- **Non-blocking operations**: All operations yield control to allow other tasks to run
- **Resource management**: Automatic cleanup of temporary files and resources via `GracefulShutdown`
- **Error handling**: Comprehensive exception types for different failure scenarios
- **Concurrent operations**: Full support for running multiple directory operations concurrently
- **Stream-like interfaces**: Generator-based APIs for processing large directory trees

## Core Operations

### Directory Creation

```php
use venndev\vosaka\fs\Folder;

// Create directory with default permissions (0755)
yield from Folder::createDir('/path/to/directory');

// Create directory with custom permissions
yield from Folder::createDir('/path/to/directory', 0644);

// Create directory without parent creation
yield from Folder::createDir('/path/to/directory', 0755, false);
```

### Directory Removal

```php
// Remove directory and all contents recursively
yield from Folder::removeDir('/path/to/directory');

// Remove empty directory only
yield from Folder::removeDir('/path/to/directory', false);
```

### Reading Directory Contents

```php
// Read directory entries asynchronously
$reader = Folder::readDir('/path/to/directory');
foreach ($reader as $fileInfo) {
    echo $fileInfo->getFilename() . "\n";

    if ($fileInfo->isDir()) {
        echo "  [Directory]\n";
    } else {
        echo "  [File] Size: " . $fileInfo->getSize() . " bytes\n";
    }
}

// Include hidden files
$reader = Folder::readDir('/path/to/directory', true);
```

### Walking Directory Trees

```php
// Walk directory tree recursively
$walker = Folder::walkDir('/path/to/directory');
foreach ($walker as $fileInfo) {
    echo $fileInfo->getPathname() . "\n";
}

// Walk with depth limit
$walker = Folder::walkDir('/path/to/directory', 2); // Max depth 2

// Walk with custom filter
$walker = Folder::walkDir('/path/to/directory', -1, function($fileInfo) {
    return $fileInfo->isFile() && pathinfo($fileInfo->getFilename(), PATHINFO_EXTENSION) === 'php';
});
```

**Similar to**: `walkdir` crate functionality in Rust

## Advanced Operations

### Directory Copying

```php
// Copy directory recursively
$copiedFiles = yield from Folder::copyDir('/source/path', '/destination/path');
echo "Copied {$copiedFiles} files\n";

// Copy with overwrite protection
$copiedFiles = yield from Folder::copyDir('/source/path', '/destination/path', false);
```

**Features**:
- Atomic file operations using temporary files
- Automatic registration with `GracefulShutdown` for cleanup
- Progress tracking via yielded file count
- Overwrite protection

### Directory Moving

```php
// Move/rename directory
yield from Folder::moveDir('/old/path', '/new/path');
```

**Features**:
- Attempts atomic rename first
- Falls back to copy+delete for cross-filesystem moves
- Proper error handling for existing destinations

### Directory Watching

```php
// Watch directory for changes
$watcher = Folder::watchDir('/path/to/watch', 1.0); // Poll every second

foreach ($watcher as $event) {
    yield;

    if (!Expect::c($event, "array")) {
        continue;
    }

    echo "Change detected: {$event['type']} - {$event['path']}\n";

    switch ($event['type']) {
        case 'created':
            echo "New file/directory created\n";
            break;

        case 'modified':
            echo "File/directory modified\n";
            break;

        case 'deleted':
            echo "File/directory deleted\n";
            break;
    }
}

// Watch with custom filter
$watcher = Folder::watchDir('/path/to/watch', 1.0, function($event) {
    return str_ends_with($event['path'], '.php');
});
```

## Resource Management

### Temporary Directories

```php
// Create temporary directory (auto-cleanup on shutdown)
$tempDir = yield from Folder::createTempDir();

// Create with custom prefix
$tempDir = yield from Folder::createTempDir('myapp_');

// Create in specific temp directory
$tempDir = yield from Folder::createTempDir('myapp_', '/custom/temp/path');
```

**Features**:
- Unique directory names using `uniqid()` and random numbers
- Automatic registration with `GracefulShutdown` for cleanup
- Customizable prefix and base directory

### Directory Locking

```php
// Acquire exclusive lock on directory
$lockHandle = yield from Folder::lockDir('/path/to/directory', 30.0); // 30 second timeout

try {
    // Perform operations while holding lock
    yield from someOperation();

} finally {
    // Always release the lock
    yield from Folder::unlockDir($lockHandle, '/path/to/directory');
}
```

**Features**:
- Lock files automatically cleaned up by `GracefulShutdown`
- Configurable timeout for lock acquisition
- Lock information stored in JSON format

## Utility Operations

### Directory Metadata

```php
$metadata = yield from Folder::metadata('/path/to/directory');

echo "Path: {$metadata['path']}\n";
echo "Size: {$metadata['size']} bytes\n";
echo "Permissions: " . sprintf('%o', $metadata['permissions']) . "\n";
echo "Owner: {$metadata['owner']}\n";
echo "Group: {$metadata['group']}\n";
echo "Last modified: " . date('Y-m-d H:i:s', $metadata['modified']) . "\n";
echo "Readable: " . ($metadata['is_readable'] ? 'Yes' : 'No') . "\n";
echo "Writable: " . ($metadata['is_writable'] ? 'Yes' : 'No') . "\n";
echo "Executable: " . ($metadata['is_executable'] ? 'Yes' : 'No') . "\n";
```

### Directory Size Calculation

```php
// Calculate total size of directory recursively
$totalSize = yield from Folder::calculateSize('/path/to/directory');
echo "Directory size: {$totalSize} bytes\n";
```

### File Finding

```php
// Find files matching pattern
$finder = Folder::find('/path/to/search', '*.php', true); // Recursive

foreach ($finder as $file) {
    echo "Found PHP file: {$file->getPathname()}\n";
}

// Non-recursive search
$finder = Folder::find('/path/to/search', '*.txt', false);
```

## Concurrent Operations

All folder operations can be used with VOsaka's concurrency primitives:

### Parallel Directory Creation

```php
use venndev\vosaka\VOsaka;

// Create multiple directories concurrently
$tasks = [
    fn() => Folder::createDir('/path/1'),
    fn() => Folder::createDir('/path/2'),
    fn() => Folder::createDir('/path/3'),
];

yield from VOsaka::join(...$tasks)->unwrap();
```

### Racing Operations

```php
// Race between directory operations and timeout
$result = yield from VOsaka::selectTimeout(5.0,
    fn() => Folder::copyDir('/large/source', '/destination'),
    fn() => Folder::calculateSize('/another/directory')
)->unwrap();

if ($result === null) {
    echo "Operations timed out after 5 seconds\n";
} else {
    [$index, $value] = $result;
    echo "Operation {$index} completed first with result: {$value}\n";
}
```

## Error Handling

The folder operations use comprehensive exception types:

### DirectoryException

```php
try {
    yield from Folder::createDir('/restricted/path');
} catch (DirectoryException $e) {
    echo "Directory operation failed: " . $e->getMessage() . "\n";
    echo "Operation: " . $e->getDirectoryOperation() . "\n";
    echo "Path: " . $e->getPath() . "\n";
}
```

### LockException

```php
try {
    $lock = yield from Folder::lockDir('/path', 1.0); // 1 second timeout
} catch (LockException $e) {
    echo "Lock acquisition failed: " . $e->getMessage() . "\n";
    echo "Timeout: " . $e->getTimeout() . " seconds\n";
}
```

### InvalidPathException

```php
try {
    yield from Folder::createDir('');
} catch (InvalidPathException $e) {
    echo "Invalid path: " . $e->getMessage() . "\n";
    echo "Validation type: " . $e->getValidationType() . "\n";
}
```

## GracefulShutdown Integration

All operations that create temporary files, locks, or other resources automatically integrate with VOsaka's `GracefulShutdown` system:

### Automatic Resource Cleanup

- **Temporary directories**: Automatically removed on shutdown
- **Lock files**: Automatically cleaned up on shutdown
- **Temporary files**: Created during copy operations are tracked and cleaned up
- **Stream resources**: Any open file handles are properly closed

### Manual Cleanup

```php
$gracefulShutdown = VOsaka::getLoop()->getGracefulShutdown();

// Force cleanup of invalid resources only
$gracefulShutdown->cleanup();

// Force full cleanup
$gracefulShutdown->cleanupAll();
```

## Performance Considerations

### Chunked Processing

All operations that process large numbers of files yield control periodically:

```php
// Reading large directories yields every 50 entries
// Walking large trees yields every 100 entries
// Copying operations yield every 50 files
```

### Memory Usage

- Directory reading uses iterators to avoid loading all entries into memory
- Tree walking uses `RecursiveIteratorIterator` for memory efficiency
- File copying uses chunked operations to handle large files

### Concurrency

Operations can be easily parallelized using VOsaka's concurrency primitives:

```php
// Process multiple directories concurrently
$tasks = array_map(function($dir) {
    return fn() => Folder::calculateSize($dir);
}, $directories);

$sizes = yield from VOsaka::join(...$tasks)->unwrap();
```

## Best Practices

### Error Handling

Always wrap operations in try-catch blocks and handle specific exception types:

```php
try {
    yield from Folder::copyDir($source, $destination);
} catch (DirectoryException $e) {
    // Handle directory-specific errors
} catch (FileIOException $e) {
    // Handle I/O errors during file operations
} catch (InvalidPathException $e) {
    // Handle invalid path errors
}
```

### Resource Management

Use try-finally blocks for operations that acquire resources:

```php
$lockHandle = null;
try {
    $lockHandle = yield from Folder::lockDir($path);
    // Perform locked operations
} finally {
    if ($lockHandle) {
        yield from Folder::unlockDir($lockHandle, $path);
    }
}
```

### Large Directory Processing

For large directories, consider using filters and depth limits:

```php
// Limit depth to avoid deep recursion
$walker = Folder::walkDir($path, 5);

// Use filters to reduce processing
$walker = Folder::walkDir($path, -1, function($file) {
    return $file->isFile() && $file->getSize() > 1024; // Files > 1KB only
});
```

## Examples

See `examples/folder-example.php` for comprehensive examples demonstrating:

- Basic CRUD operations
- Concurrent processing
- Error handling patterns
- Resource management
- Directory watching
- Performance optimization techniques

## Migration from Synchronous Code

### Before (Synchronous)

```php
// Blocking operations
mkdir('/path/to/dir', 0755, true);
$files = scandir('/path/to/dir');
copy('/source', '/dest');
rmdir('/path/to/dir');
```

### After (Asynchronous)

```php
// Non-blocking operations
yield from Folder::createDir('/path/to/dir');

$reader = Folder::readDir('/path/to/dir');
foreach ($reader as $file) {
    // Process each file
}

yield from Folder::copyDir('/source', '/dest');
yield from Folder::removeDir('/path/to/dir');
```

The asynchronous version allows other tasks to run while directory operations are in progress, leading to better overall application performance and responsiveness.
