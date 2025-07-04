<?php
require "../vendor/autoload.php";

use venndev\vosaka\net\tcp\TCPStream;
use venndev\vosaka\VOsaka;
use venndev\vosaka\net\tcp\TCPListener;
use venndev\vosaka\time\Sleep;

function handleClient(TCPStream $client): Generator
{
    $clientAddr = $client->peerAddr();
    // echo "Client connected: $clientAddr\n";

    try {
        while (!$client->isClosed()) {
            $data = yield from $client->read(1024)->unwrap();

            if ($data === null || $data === "") {
                // echo "Client $clientAddr disconnected\n";
                break;
            }

            // echo "Received from $clientAddr: " . trim($data) . "\n";

            // Send response
            $response = "HTTP/1.1 200 OK\r\n";
            $response .= "Content-Type: text/plain\r\n";
            $response .=
                "Content-Length: " . strlen("Hello from VOsaka!") . "\r\n";
            $response .= "\r\n";
            $response .= "Hello from VOsaka!";

            $bytesWritten = yield from $client->writeAll($response)->unwrap();
            // echo "Sent $bytesWritten bytes to $clientAddr\n";

            yield;
        }
    } catch (Exception $e) {
        echo "Error handling client $clientAddr: " . $e->getMessage() . "\n";
    } finally {
        if (!$client->isClosed()) {
            $client->close();
        }
        // echo "Client $clientAddr connection closed\n";
    }
}

function main(): Generator
{
    // Enhanced TCP listener with better options
    $listener = yield from TCPListener::bind("0.0.0.0:8099", [
        "accept_timeout" => 1, // 100ms accept timeout
        "max_connections" => 100, // Max 100 concurrent connections
        "read_timeout" => 60, // 60 seconds read timeout
        "write_timeout" => 30, // 30 seconds write timeout
        "buffer_size" => 4096, // 4KB buffer size
        "nodelay" => true, // TCP_NODELAY
        "reuseaddr" => true, // SO_REUSEADDR
    ])->unwrap();

    echo "Improved TCP Server listening on 0.0.0.0:8099\n";
    echo "Max connections: " .
        $listener->getOptions()["max_connections"] .
        "\n";
    echo "Accept timeout: " . $listener->getOptions()["accept_timeout"] . "s\n";

    $connectionCount = 0;

    while (!$listener->isClosed()) {
        try {
            $client = yield from $listener->accept()->unwrap();

            if ($client === null) {
                // No client available, add small delay to prevent busy loop
                yield Sleep::new(0.01);
                continue;
            }

            if ($client->isClosed()) {
                continue;
            }

            $connectionCount++;
            // echo "New client accepted\n";

            // Spawn client handler
            VOsaka::spawn(handleClient($client));
        } catch (Exception $e) {
            echo "Error accepting client: " . $e->getMessage() . "\n";
            yield Sleep::new(0.1); // Wait a bit before retrying
        }
    }
}

// Graceful shutdown handler
function shutdownHandler(): void
{
    echo "\nShutting down server...\n";
    VOsaka::getLoop()->stop();
}

echo "Starting improved TCP server...\n";
echo "Press Ctrl+C to stop\n\n";

VOsaka::spawn(main());
VOsaka::run();
