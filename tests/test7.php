<?php
require "../vendor/autoload.php";
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
