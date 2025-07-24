<?php
require "../vendor/autoload.php";

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
