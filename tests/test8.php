<?php

function createSocketPairWindows(): array
{
    $server = stream_socket_server("tcp://127.0.0.1:0", $errno, $errstr);
    if (!$server) {
        throw new Exception("Could not create server: $errstr");
    }

    $address = stream_socket_get_name($server, false);
    $client = stream_socket_client("tcp://$address", $errno, $errstr, 1);
    if (!$client) {
        throw new Exception("Could not connect to server: $errstr");
    }

    $serverConn = stream_socket_accept($server, 1);
    fclose($server);

    if (!$serverConn) {
        throw new Exception("Failed to accept connection");
    }

    return [$serverConn, $client];
}

function pollingTask($stream): Generator
{
    while (true) {
        $r = [$stream];
        $w = $e = [];
        stream_select($r, $w, $e, 0, 0); // non-blocking
        if (!empty($r)) {
            $data = fread($stream, 1024);
            break;
        }
        yield;
    }
}

function sleepingTask($stream): Generator
{
    while (true) {
        $r = [$stream];
        $w = $e = [];
        yield function () use (&$r, &$w, &$e) {
            return stream_select($r, $w, $e, null);
        };
        $data = fread($stream, 1024);
        break;
    }
}

function runEventLoop(array $tasks): void
{
    $start = microtime(true);
    while (!empty($tasks)) {
        foreach ($tasks as $i => &$task) {
            $value = $task->current();

            if (is_callable($value)) {
                $ready = $value();
                if ($ready === false) {
                    continue;
                }
            }

            $task->next();

            if (!$task->valid()) {
                unset($tasks[$i]);
            }
        }
    }
    $ms = round((microtime(true) - $start) * 1000, 2);
    echo "Done in {$ms} ms\n";
}

function testManyTasks(callable $taskFactory, string $label, int $count = 100)
{
    echo "== $label ($count tasks) ==\n";
    $tasks = [];
    $writers = [];
    for ($i = 0; $i < $count; $i++) {
        [$reader, $writer] = createSocketPairWindows();
        stream_set_blocking($reader, false);
        stream_set_blocking($writer, false);
        $tasks[] = $taskFactory($reader);
        $writers[] = $writer;
    }

    // Delay một chút rồi gửi dữ liệu đồng loạt
    usleep(100_000);
    foreach ($writers as $w) {
        fwrite($w, "Hi");
    }

    runEventLoop($tasks);
    echo "\n";
}

// 🧪 Thử lại với 100 task
testManyTasks(fn($r) => pollingTask($r), "Polling", 100);
testManyTasks(fn($r) => sleepingTask($r), "Sleeping", 100);
