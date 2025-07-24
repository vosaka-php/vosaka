# VOsaka

[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.1-brightgreen)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-blue)](LICENSE)
[![API Docs](https://img.shields.io/badge/docs-GitBook-orange)](https://venndev.gitbook.io/vosaka)

A high-performance, event-driven asynchronous runtime for PHP that brings modern async/await patterns to PHP development. VOsaka provides a pure PHP solution for building concurrent, scalable applications without requiring additional extensions.

## ✨ Features

- **🚀 Pure PHP Implementation** - No extensions required, works with any PHP 8.1+ setup
- **⚡ High Performance** - Optimized for handling thousands of concurrent operations
- **🔧 Easy to Use** - Simple, intuitive API inspired by modern async runtimes
- **🌐 Network Support** - Built-in TCP/UDP socket handling with non-blocking I/O
- **📁 File System Operations** - Asynchronous file and directory operations
- **🔄 Process Management** - Spawn and manage system processes asynchronously
- **⏱️ Time Management** - Built-in timers, delays, and scheduling
- **🔒 Synchronization** - Channels, locks, and other sync primitives
- **🛡️ Error Handling** - Robust error handling with Result types

## 🚀 Quick Start

### Installation

```bash
composer require venndev/v-osaka
```

### Basic Usage

```php
<?php
require 'vendor/autoload.php';

use venndev\vosaka\VOsaka;
use venndev\vosaka\time\Sleep;

function main(): Generator {
    echo "Hello from VOsaka!\n";
    yield Sleep::new(1.0); // Sleep for 1 second
    echo "Async operation complete!\n";
}

VOsaka::spawn(main());
VOsaka::run();
```

## 📚 Core Concepts

### Generators & Async Functions

VOsaka uses PHP generators to provide async/await-like functionality:

```php
function asyncOperation(): Generator {
    // Async operations use 'yield from'
    $result = yield from someAsyncCall();
    return $result;
}
```

### Spawning Tasks

Tasks are spawned using `VOsaka::spawn()`:

```php
// Spawn a single task
VOsaka::spawn(myAsyncFunction());

// Spawn multiple tasks concurrently
VOsaka::spawn(task1());
VOsaka::spawn(task2());
VOsaka::spawn(task3());
```

### Error Handling

VOsaka uses Result types for robust error handling:

```php
function safeOperation(): Generator {
    $result = yield from riskyOperation()->unwrap();
    // Use unwrap() to get the value or throw on error
}
```

## 🌐 Network Programming

### TCP Server

```php
use venndev\vosaka\VOsaka;
use venndev\vosaka\net\tcp\TCP;
use venndev\vosaka\net\tcp\TCPConnection;
use venndev\vosaka\net\tcp\TCPServer;

function handleClient(TCPConnection $client): Generator
{
    while (!$client->isClosed()) {
        $data = yield from $client->read(1024)->unwrap();

        if ($data === null || $data === "") {
            echo "Client disconnected\n";
            break;
        }

        echo "Received: $data\n";

        $bytesWritten = yield from $client
            ->writeAll("Hello from VOsaka!\n")
            ->unwrap();
        echo "Sent: $bytesWritten bytes\n";
    }

    if (!$client->isClosed()) {
        $client->close();
    }
    echo "Client connection closed\n";
}

function main(): Generator
{
    /**
     * @var TCPServer $listener
     */
    $listener = yield from TCP::listen("0.0.0.0:8099")->unwrap();
    echo "Server listening on 127.0.0.1:8099\n";

    while (!$listener->isClosed()) {
        /**
         * @var TCPConnection|null $client
         */
        $client = yield from $listener->accept()->unwrap();

        if ($client !== null && !$client->isClosed()) {
            echo "New client connected\n";
            VOsaka::spawn(handleClient($client));
        }

        yield;
    }
}

VOsaka::spawn(main());
VOsaka::run();
```

### TCP Client

```php
use venndev\vosaka\net\tcp\TCP;
use venndev\vosaka\VOsaka;
use venndev\vosaka\time\Sleep;

function interactiveClient(): Generator
{
    try {
        echo "Connecting to server at 127.0.0.1:8099...\n";

        $stream = yield from TCP::connect("127.0.0.1:8099")->unwrap();
        echo "Connected successfully!\n";
        echo "Type messages to send to server (type 'quit' to exit):\n\n";

        // Spawn a task to handle incoming messages
        VOsaka::spawn(handleIncomingMessages($stream));

        // Main input loop
        while (true) {
            echo "> ";
            $input = trim(fgets(STDIN));

            if ($input === "quit" || $input === "exit") {
                echo "Disconnecting...\n";
                break;
            }

            if ($input === "") {
                continue;
            }

            try {
                $bytesWritten = yield from $stream->writeAll($input)->unwrap();
                echo "Sent: $bytesWritten bytes\n";
            } catch (Exception $e) {
                echo "Failed to send message: " . $e->getMessage() . "\n";
                break;
            }

            yield Sleep::new(0.01); // Small delay
        }

        $stream->close();
        echo "Connection closed.\n";
    } catch (Exception $e) {
        echo "Connection error: " . $e->getMessage() . "\n";
    }
}

function handleIncomingMessages($stream): Generator
{
    while (!$stream->isClosed()) {
        try {
            $response = yield from $stream->read(1024)->unwrap();

            if ($response === null) {
                echo "\nServer disconnected.\n";
                break;
            }

            if ($response !== "") {
                echo "\nServer response: $response";
                echo "> "; // Re-prompt for input
            }
        } catch (Exception $e) {
            echo "\nError reading from server: " . $e->getMessage() . "\n";
            break;
        }

        yield Sleep::new(0.01);
    }
}

function main(): Generator
{
    echo "=== Interactive TCP Client for VOsaka Server ===\n";
    yield from interactiveClient();
    echo "Client terminated.\n";
}

VOsaka::spawn(main());
VOsaka::run();
```

### UDP Operations

```php
use venndev\vosaka\net\udp\UDP;
use venndev\vosaka\net\udp\UDPSocket;
use venndev\vosaka\VOsaka;

function udpServer(): Generator
{
    /**
     * @var UDPSocket $socket
     */
    $socket = yield from UDP::bind('127.0.0.1:12345')->unwrap();
    echo "UDP server started on {$socket->getLocalAddress()->toString()}\n";

    while (true) {
        $result = yield from $socket->receiveFrom(1024)->unwrap();
        $data = $result['data'];
        $addr = $result['peerAddr'];
        echo "Received from $addr: $data\n";
        yield from $socket->sendTo("Echo: $data", $addr)->unwrap();
    }
}

```

## 📁 File System Operations

### File Operations

```php
use venndev\vosaka\fs\File;

function fileOperations(): Generator {
    // Read file asynchronously
    $content = yield from File::read("example.txt")->unwrap();

    // Write file asynchronously
    yield from File::write("output.txt", "Hello World!")->unwrap();

    // Read and append to file (since no direct append method)
    $existing = yield from File::read("output.txt")->unwrap();
    yield from File::write("output.txt", $existing . "\nAppended content")->unwrap();
}
```

### Directory Operations

```php
use venndev\vosaka\fs\Folder;

function directoryOperations(): Generator {
    // Create directory
    yield from Folder::createDir("new_folder")->unwrap();

    // Read directory contents
    $entries = Folder::readDir(".")->unwrap();
    foreach ($entries as $entry) {
        echo ($entry->isDir() ? "DIR: " : "FILE: ") . $entry->getFilename() . "\n";
    }

    // Copy directory recursively
    yield from Folder::copyDir("source", "destination")->unwrap();

    // Remove directory
    yield from Folder::removeDir("old_folder")->unwrap();
}
```

## 🔄 Process Management

```php
use venndev\vosaka\process\Process;

function processExample(): Generator {
    $process = new Process();

    // Start the process
    yield from $process->start("ls -la")->unwrap();

    // Wait for process to complete
    $exitCode = yield from $process->wait()->unwrap();

    // Read output
    $output = yield from $process->readStdout()->unwrap();
    echo "Process output: $output\n";
    echo "Exit code: $exitCode\n";
}
```

## ⏱️ Time & Scheduling

```php
use venndev\vosaka\time\{Sleep, Interval, Timeout};

function timeExamples(): Generator {
    // Sleep for 2 seconds
    yield Sleep::new(2.0);

    // Sleep for 500 milliseconds
    yield Sleep::ms(500);

    // Sleep for 100 microseconds
    yield Sleep::us(100);
}
```

## 🔄 Concurrent Operations

### JoinSet for Task Management

```php
use venndev\vosaka\task\JoinSet;

function concurrentTasks(): Generator {
    $joinSet = JoinSet::new();

    // Spawn multiple tasks
    yield from $joinSet->spawn(task1())->unwrap();
    yield from $joinSet->spawn(task2())->unwrap();
    yield from $joinSet->spawn(task3())->unwrap();

    // Wait for all tasks to complete
    $results = yield from $joinSet->joinAll()->unwrap();

    foreach ($results as $taskId => $result) {
        echo "Task $taskId result: $result\n";
    }
}
```

### Join Operations

```php
// Wait for multiple operations to complete
function multipleOperations(): Generator {
    $task1 = function(): Generator {
        yield Sleep::new(1.0);
        return "Result 1";
    };

    $task2 = function(): Generator {
        yield Sleep::new(2.0);
        return "Result 2";
    };

    $task3 = function(): Generator {
        yield Sleep::new(1.5);
        return "Result 3";
    };

    $results = yield from VOsaka::join($task1, $task2, $task3)->unwrap();
    [$result1, $result2, $result3] = $results;
    echo "Results: $result1, $result2, $result3\n";
}
```

## 🔒 Synchronization

### Channels

```php
use venndev\vosaka\sync\Channel;

function channelExample(): Generator {
    $channel = Channel::new(10); // Channel with capacity of 10

    // Producer task
    VOsaka::spawn(function() use ($channel): Generator {
        for ($i = 0; $i < 10; $i++) {
            yield from $channel->send($i)->unwrap();
            yield from Sleep::new(0.1)->toGenerator();
        }
        $channel->close();
    });

    // Consumer task
    while (true) {
        try {
            $value = yield from $channel->receive()->unwrap();
            echo "Received: $value\n";
        } catch (Exception $e) {
            break; // Channel closed
        }
    }
}
```

## 🛡️ Error Handling Patterns

### Result Types

```php
function safeOperation(): Generator {
    try {
        $result = yield from riskyOperation()->unwrap();
        return $result;
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        return null;
    }
}

function betterErrorHandling(): Generator {
    try {
        $result = yield from riskyOperation()->unwrap();
        // Use result...
        echo "Operation succeeded: $result\n";
    } catch (Exception $e) {
        echo "Operation failed: " . $e->getMessage() . "\n";
    }
}
```

## 📊 Performance Tips

1. **Batch Operations**: Group related operations together
2. **Use Channels**: For producer-consumer patterns
3. **Avoid Blocking**: Never use blocking operations in async code
4. **Resource Management**: Always close resources properly
5. **Error Handling**: Use Result types for better error handling

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details.

## 📄 License

VOsaka is open-sourced software licensed under the [MIT license](LICENSE).

## 🔗 Links

- [API Documentation](https://venndev.gitbook.io/vosaka)
- [GitHub Repository](https://github.com/vosaka-php/vosaka)
- [Issue Tracker](https://github.com/vosaka-php/vosaka/issues)
