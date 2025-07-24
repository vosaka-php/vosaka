<?php

declare(strict_types=1);

require_once __DIR__ . "/../vendor/autoload.php";

use venndev\vosaka\VOsaka;
use venndev\vosaka\fs\Folder;
use venndev\vosaka\sync\mpsc\Expect;
use venndev\vosaka\time\Sleep;

/**
 * This example showcases various folder operations that are inspired by
 * Filesystem module, including:
 * - Creating and removing directories
 * - Reading directory contents asynchronously
 * - Walking directory trees
 * - Copying and moving directories
 * - Watching for changes
 * - Working with temporary directories
 * - Directory locking
 */

// Example 1: Basic directory operations
function basicOperations(): Generator
{
    echo "=== Basic Directory Operations ===\n";

    $testDir = __DIR__ . "/test_folder";

    echo "Creating directory: {$testDir}\n";
    yield from Folder::createDir($testDir)->unwrap();

    // Create some subdirectories
    yield from Folder::createDir($testDir . "/subdir1")->unwrap();
    yield from Folder::createDir($testDir . "/subdir2/nested")->unwrap();

    // Create some test files
    file_put_contents($testDir . "/file1.txt", "Hello World");
    file_put_contents($testDir . "/subdir1/file2.txt", "Test Content");

    echo "Directory structure created successfully!\n\n";

    return $testDir;
}

// Example 2: Reading directory contents
function readDirectory(string $path): Generator
{
    echo "=== Reading Directory Contents ===\n";
    echo "Contents of {$path}:\n";

    $reader = Folder::readDir($path)->unwrap();
    foreach ($reader as $entry) {
        $type = $entry->isDir() ? "DIR" : "FILE";
        $size = $entry->isFile() ? $entry->getSize() : 0;
        echo "  [{$type}] {$entry->getFilename()} ({$size} bytes)\n";
    }
    echo "\n";

    yield;
}

// Example 3: Walking directory tree
function walkDirectory(string $path): Generator
{
    echo "=== Walking Directory Tree ===\n";
    echo "Walking tree from {$path}:\n";

    // Walk directory recursively (similar to walkdir in Rust)
    $walker = Folder::walkDir($path, 2)->unwrap(); // Max depth 2
    foreach ($walker as $entry) {
        $type = $entry->isDir() ? "DIR" : "FILE";
        $relativePath = str_replace(
            $path . DIRECTORY_SEPARATOR,
            "",
            $entry->getPathname()
        );
        echo "  [{$type}] {$relativePath}\n";
    }
    echo "\n";

    yield;
}

// Example 4: Directory copying
function copyDirectory(string $source, string $destination): Generator
{
    echo "=== Copying Directory ===\n";
    echo "Copying {$source} to {$destination}\n";

    // Copy directory recursively with progress
    $copiedFiles = yield from Folder::copyDir($source, $destination)->unwrap();
    echo "Successfully copied {$copiedFiles} files\n\n";

    yield;
}

// Example 5: Working with temporary directories
function tempDirectoryExample(): Generator
{
    echo "=== Temporary Directory Example ===\n";

    // Create temporary directory (automatically cleaned up by GracefulShutdown)
    $tempDir = yield from Folder::createTempDir("example_")->unwrap();
    echo "Created temporary directory: {$tempDir}\n";

    // Create some files in temp directory
    file_put_contents($tempDir . "/temp_file.txt", "Temporary content");
    yield from Folder::createDir($tempDir . "/temp_subdir")->unwrap();

    echo "Temporary directory will be cleaned up automatically on shutdown\n\n";

    return $tempDir;
}

// Example 6: Directory locking
function lockingExample(string $path): Generator
{
    echo "=== Directory Locking Example ===\n";

    try {
        // Lock directory for exclusive access
        echo "Acquiring lock on {$path}\n";
        $lockHandle = yield from Folder::lockDir($path, 5.0)->unwrap(); // 5 second timeout

        echo "Lock acquired! Performing operations...\n";

        // Simulate some work
        yield Sleep::new(1.0);

        // Release lock
        yield from Folder::unlockDir($lockHandle, $path)->unwrap();
        echo "Lock released\n\n";
    } catch (Exception $e) {
        echo "Failed to acquire lock: " . $e->getMessage() . "\n\n";
    }

    yield;
}

// Example 7: Directory metadata
function metadataExample(string $path): Generator
{
    echo "=== Directory Metadata Example ===\n";

    $metadata = yield from Folder::metadata($path)->unwrap();

    echo "Metadata for {$path}:\n";
    echo "  Path: {$metadata["path"]}\n";
    echo "  Size: {$metadata["size"]} bytes\n";
    echo "  Permissions: " . sprintf("%o", $metadata["permissions"]) . "\n";
    echo "  Owner: {$metadata["owner"]}\n";
    echo "  Group: {$metadata["group"]}\n";
    echo "  Last modified: " .
        date("Y-m-d H:i:s", $metadata["modified"]) .
        "\n";
    echo "  Readable: " . ($metadata["is_readable"] ? "Yes" : "No") . "\n";
    echo "  Writable: " . ($metadata["is_writable"] ? "Yes" : "No") . "\n";
    echo "  Executable: " .
        ($metadata["is_executable"] ? "Yes" : "No") .
        "\n\n";

    yield;
}

// Example 8: Finding files with patterns
function findFilesExample(string $path): Generator
{
    echo "=== Finding Files Example ===\n";
    echo "Finding .txt files in {$path}:\n";

    $finder = Folder::find($path, "*.txt", true)->unwrap();
    foreach ($finder as $file) {
        echo "  Found: {$file->getPathname()}\n";
    }
    echo "\n";

    yield;
}

// Example 9: Calculate directory size
function calculateSizeExample(string $path): Generator
{
    echo "=== Calculate Directory Size ===\n";

    $size = yield from Folder::calculateSize($path)->unwrap();
    $sizeKB = round($size / 1024, 2);

    echo "Directory {$path} size: {$size} bytes ({$sizeKB} KB)\n\n";

    yield;
}

// Example 10: Directory watching (simplified example)
function watchingExample(string $path): Generator
{
    echo "=== Directory Watching Example ===\n";
    echo "Watching {$path} for changes (will run for 5 seconds)...\n";

    // Start watching in the background
    $watcher = Folder::watchDir($path, 0.5)->unwrap(); // Poll every 500ms

    $startTime = microtime(true);
    $maxDuration = 5.0; // Watch for 5 seconds

    foreach ($watcher as $event) {
        yield;

        if (!Expect::new($event, "array")) {
            continue;
        }

        echo "  Change detected: {$event["type"]} - {$event["path"]}\n";

        // Make a change to demonstrate
        if (
            microtime(true) - $startTime > 2.0 &&
            microtime(true) - $startTime < 2.5
        ) {
            file_put_contents(
                $path . "/watch_test.txt",
                "Test file for watching"
            );
        }

        if (microtime(true) - $startTime > $maxDuration) {
            break;
        }
    }

    echo "Watching stopped\n\n";

    yield;
}

// Main execution function
function main(): Generator
{
    echo "Folder Operations Example\n";
    echo "========================================\n\n";

    // Run basic operations
    $testDir = yield from basicOperations();

    // Read directory
    yield from readDirectory($testDir);

    // Walk directory tree
    yield from walkDirectory($testDir);

    // Copy directory
    $copyDir = $testDir . "_copy";
    yield from copyDirectory($testDir, $copyDir);

    // Temporary directory example
    $tempDir = yield from tempDirectoryExample();

    // Locking example
    yield from lockingExample($testDir);

    // Metadata example
    yield from metadataExample($testDir);

    // Find files example
    yield from findFilesExample($testDir);

    // Calculate size example
    yield from calculateSizeExample($testDir);

    // Directory watching example
    yield from watchingExample($testDir);

    // Cleanup
    echo "=== Cleanup ===\n";
    yield from Folder::removeDir($testDir)->unwrap();
    yield from Folder::removeDir($copyDir)->unwrap();
    echo "Test directories removed\n";

    echo "\nExample completed successfully!\n";
    echo "Note: Temporary directories and lock files are automatically cleaned up by GracefulShutdown\n";

    yield;
}

// Advanced example showing concurrent operations
function concurrentExample(): Generator
{
    echo "\n=== Concurrent Operations Example ===\n";

    // Create multiple directories concurrently using VOsaka::join
    $tasks = [];
    for ($i = 1; $i <= 3; $i++) {
        $tasks[] = function () use ($i): Generator {
            $dir = __DIR__ . "/concurrent_test_{$i}";
            yield from Folder::createDir($dir)->unwrap();

            // Create some files
            for ($j = 1; $j <= 5; $j++) {
                file_put_contents($dir . "/file_{$j}.txt", "Content {$i}-{$j}");
                yield Sleep::ms(10); // Small delay to show async behavior
            }

            return $dir;
        };
    }

    echo "Creating 3 directories concurrently...\n";
    $results = yield from VOsaka::join(...$tasks)->unwrap();

    echo "All directories created: " . implode(", ", $results) . "\n";

    // Calculate sizes concurrently
    $sizeTasks = array_map(function ($dir) {
        return function () use ($dir): Generator {
            $size = yield from Folder::calculateSize($dir)->unwrap();
            return [$dir, $size];
        };
    }, $results);

    $sizeResults = yield from VOsaka::join(...$sizeTasks)->unwrap();

    echo "Directory sizes:\n";
    foreach ($sizeResults as [$dir, $size]) {
        $dirName = basename($dir);
        echo "  {$dirName}: {$size} bytes\n";
    }

    // Cleanup concurrently
    $cleanupTasks = array_map(function ($dir) {
        return function () use ($dir): Generator {
            yield from Folder::removeDir($dir)->unwrap();
            return basename($dir);
        };
    }, $results);

    $cleanedDirs = yield from VOsaka::join(...$cleanupTasks)->unwrap();
    echo "Cleaned up directories: " . implode(", ", $cleanedDirs) . "\n";

    yield;
}

// Error handling example
function errorHandlingExample(): Generator
{
    echo "\n=== Error Handling Example ===\n";

    try {
        // Try to read a non-existent directory
        echo "Attempting to read non-existent directory...\n";
        $reader = Folder::readDir("/non/existent/directory")->unwrap();
        foreach ($reader as $entry) {
            // This won't execute
        }
    } catch (Exception $e) {
        echo "Caught expected error: " . $e->getMessage() . "\n";
    }

    try {
        // Try to create directory in restricted location (might fail)
        echo "Attempting to create directory in restricted location...\n";
        yield from Folder::createDir("/restricted/path")->unwrap();
    } catch (Exception $e) {
        echo "Caught expected error: " . $e->getMessage() . "\n";
    }

    echo "Error handling example completed\n";

    yield;
}

// Run the examples
try {
    echo "Starting Folder Operations Example...\n\n";

    // Run main example
    VOsaka::spawn(main());
    VOsaka::run();

    // Run concurrent example
    VOsaka::spawn(concurrentExample());
    VOsaka::run();

    // Run error handling example
    VOsaka::spawn(errorHandlingExample());
    VOsaka::run();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nAll examples completed!\n";
