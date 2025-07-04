<?php
require "../vendor/autoload.php";

use venndev\vosaka\net\tcp\TCP;
use venndev\vosaka\VOsaka;
use venndev\vosaka\time\Sleep;

function testClient(int $clientId): Generator
{
    try {
        echo "Client $clientId: Connecting to server...\n";

        $stream = yield from TCP::connect("127.0.0.1:8099", [
            'timeout' => 10,
            'read_timeout' => 30,
            'write_timeout' => 30,
        ])->unwrap();

        echo "Client $clientId: Connected successfully!\n";

        // Set timeouts
        $stream->setReadTimeout(30);
        $stream->setWriteTimeout(30);

        // Send HTTP request
        $request = "GET / HTTP/1.1\r\n";
        $request .= "Host: localhost:8099\r\n";
        $request .= "Connection: close\r\n";
        $request .= "\r\n";

        $bytesWritten = yield from $stream->writeAll($request)->unwrap();
        echo "Client $clientId: Sent $bytesWritten bytes\n";

        // Read response
        $response = yield from $stream->read(4096)->unwrap();
        echo "Client $clientId: Received response (".strlen($response)." bytes)\n";

        // Close connection
        $stream->close();
        echo "Client $clientId: Connection closed\n";

    } catch (Exception $e) {
        echo "Client $clientId: Error - ".$e->getMessage()."\n";
    }
}

function main(): Generator
{
    $numClients = 10; // Test with 10 concurrent clients
    echo "Testing improved TCP server with $numClients concurrent clients...\n\n";

    $startTime = microtime(true);

    // Spawn multiple clients
    for ($i = 1; $i <= $numClients; $i++) {
        VOsaka::spawn(testClient($i));
        yield Sleep::new(0.1); // Small delay between client connections
    }

    // Wait for all clients to complete
    yield Sleep::new(5);

    $endTime = microtime(true);
    $duration = $endTime - $startTime;

    echo "\nTest completed in ".round($duration, 2)." seconds\n";
    echo "All clients processed.\n";
}

echo "Starting TCP client test...\n\n";

VOsaka::spawn(main());
VOsaka::run();