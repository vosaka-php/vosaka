# VOsaka

A runtime that allows writing simple, easy-to-understand asynchronous programming for the PHP language. A synchronous runtime library for PHP. Highlights:

- **Performance**: Fast speed in handling asynchronous tasks. You won't need to worry much about performance.
- **Reliable and easy to control**: You just need to use the methods provided by this library. Easy to read and write for programs, minimizing errors with great methods.
- **Scalable**: Easily scale in your projects. Can be canceled or terminated immediately without being affected.

[API Docs](https://venndev.gitbook.io/vosaka)

# Overview

Built for speed and scalability, **VOsaka** is an event-driven async I/O platform that brings non-blocking programming to PHP. It equips developers with essential building blocks for writing concurrent, efficient systems. It provides a few major components:

- Asynchronous TCP and UDP sockets.
- Handle fs tasks such as: create files, delete files, add folders, etc.
- Create and manage processes.

# Example

First you need install from composer with cmd:

```yml
composer require venndev/v-osaka
```

Basic TCP Listener

```php
<?php
require "../vendor/autoload.php";
use venndev\vosaka\net\tcp\TCPStream;
use venndev\vosaka\VOsaka;
use venndev\vosaka\net\tcp\TCPListener;

function handleClient(TCPStream $client): Generator
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
     * @var TCPListener $listener
     */
    $listener = yield from TCPListener::bind("0.0.0.0:8099")->unwrap();
    echo "Server listening on 127.0.0.1:8099\n";

    while (!$listener->isClosed()) {
        /**
         * @var TCPStream|null $client
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
