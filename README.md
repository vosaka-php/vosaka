<<<<<<< HEAD
# VOsaka

- A synchronous runtime library for PHP
- This is a project that can replace my old chaotic universe [vamp](https://github.com/VennDev/Vapm)

# Performance
```yml
=== VOsaka Results ===
Duration: 187.5s
Successful: 4012
Failed: 988
Memory Peak: 41.08 MB
Memory Current: 4.41 MB
Completed 5000 tasks in 187.5s
Tasks/sec: 26.67
Memory used: 39.69 MB
Memory per task: 8.13 KB
```

# ReactPHP vs VOsaka (Compare Small)
```yml
=== FINAL COMPARISON RESULTS ===

Benchmark Test (100 URLs):
----------------------------
VOsaka:
  Duration: 0.32s
  Successful: 83
  Failed: 17
  Memory Peak: 0.88 MB
  Processed: 100 URLs

ReactPHP:
  Duration: 0.31s
  Successful: 79
  Failed: 21
  Memory Peak: 1.65 MB
  Memory Current: 1.01 MB
  Processed: 100 URLs

Memory Leak Test (ReactPHP, 5 batches of 50 URLs):
-----------------------------------------------
Batch 1: Memory Diff: 0.02 MB, Total Leak: 0.03 MB
Batch 2: Memory Diff: 0.02 MB, Total Leak: -0.02 MB
Batch 3: Memory Diff: 0.02 MB, Total Leak: -0.02 MB
Batch 4: Memory Diff: 0.02 MB, Total Leak: -0.02 MB
Batch 5: Memory Diff: 0.02 MB, Total Leak: -0.02 MB

Stress Test (ReactPHP):
---------------------
Tasks: 100, Duration: 0.32s, Tasks/sec: 312.5, Memory: 0.7 MB, Memory/Task: 7.22 KB, Processed: 100/100 URLs (Success: 82, Failed: 18)
Tasks: 500, Duration: 1.55s, Tasks/sec: 322.58, Memory: 0.81 MB, Memory/Task: 1.66 KB, Processed: 500/500 URLs (Success: 401, Failed: 99)
Tasks: 1000, Duration: 3.11s, Tasks/sec: 321.54, Memory: 0.84 MB, Memory/Task: 0.86 KB, Processed: 1000/1000 URLs (Success: 805, Failed: 195)
Tasks: 2000, Duration: 6.21s, Tasks/sec: 322.06, Memory: 1 MB, Memory/Task: 0.51 KB, Processed: 2000/2000 URLs (Success: 1622, Failed: 378)
Tasks: 5000, Duration: 15.53s, Tasks/sec: 321.96, Memory: 1.65 MB, Memory/Task: 0.34 KB, Processed: 5000/5000 URLs (Success: 3987, Failed: 1013)

Memory Leak Test (VOsaka, 5 batches of 50 URLs):
-----------------------------------------------
Batch 1: Memory Diff: 0.02 MB, Total Leak: 0.02 MB
Batch 2: Memory Diff: 0.02 MB, Total Leak: 0.02 MB
Batch 3: Memory Diff: 0.02 MB, Total Leak: 0.02 MB
Batch 4: Memory Diff: 0.02 MB, Total Leak: 0.02 MB
Batch 5: Memory Diff: 0.02 MB, Total Leak: 0.02 MB

Stress Test (VOsaka):
---------------------
Tasks: 100, Duration: 0.32s, Tasks/sec: 312.5, Memory: 2.88 MB, Memory/Task: 29.54 KB, Processed: 100/100 URLs (Success: 80, Failed: 20)
Tasks: 500, Duration: 1.52s, Tasks/sec: 328.95, Memory: 2.85 MB, Memory/Task: 5.84 KB, Processed: 500/500 URLs (Success: 393, Failed: 107)
Tasks: 1000, Duration: 3.04s, Tasks/sec: 328.95, Memory: 2.81 MB, Memory/Task: 2.88 KB, Processed: 1000/1000 URLs (Success: 807, Failed: 193)
Tasks: 2000, Duration: 6.09s, Tasks/sec: 328.41, Memory: 2.73 MB, Memory/Task: 1.4 KB, Processed: 2000/2000 URLs (Success: 1580, Failed: 420)
Tasks: 5000, Duration: 15.22s, Tasks/sec: 328.52, Memory: 2.46 MB, Memory/Task: 0.5 KB, Processed: 5000/5000 URLs (Success: 4030, Failed: 970)

=== Final Stats ===
VOsaka:
  Duration: 0.32s
  Successful: 83
  Failed: 17
  Memory Peak: 0.88 MB
  Memory Current: 0.78 MB
  Processed: 100 URLs

ReactPHP:
  Duration: 0.31s
  Successful: 79
  Failed: 21
  Memory Peak: 1.65 MB
  Memory Current: 1.01 MB
  Processed: 100 URLs

=== End of Tests ===
```
# Basic processing methods

- Handle tasks with Sleep, Await, Spawn, Run

```php
<?php
use venndev\vosaka\VOsaka;

function work(): Generator
{
    yield from VOsaka::sleep(1);
    yield var_dump('Work completed after 1 seconds');
    return 'Work result';
}

function workError(): Generator
{
    yield from VOsaka::sleep(1);
    yield var_dump('Work Error completed after 1 seconds');
    throw new Exception('Work Error occurred');
}

function main(): Generator
{
    $result = yield from VOsaka::await(work())();
    var_dump('Work result: ' . $result);

    // Await with error handling
    $resultError = yield from VOsaka::await(workError())();
    var_dump('Work Error result: ' . $resultError);

    // Await with default value
    $resultOrDefault = yield from VOsaka::await(workError())->unwrapOr('Default Value');
    var_dump('Work Error result with default: ' . $resultOrDefault);

    // Await with panic handling
    try {
        $resultPanic = yield from VOsaka::await(workError())->unwrap();
        var_dump($resultPanic);
    } catch (Throwable $e) {
        var_dump('Caught exception: ' . $e->getMessage());
    }

    // Await with expect handling
    try {
        $resultExpect = yield from VOsaka::await(workError())->expect('An error occurred during work');
        var_dump($resultExpect);
    } catch (RuntimeException $e) {
        var_dump('Caught RuntimeException: ' . $e->getMessage());
    }
}

VOsaka::spawn(main());
VOsaka::run();
```

- Handle tasks with Defer, Sleep, Join functions

```php
<?php
use venndev\vosaka\VOsaka;

function send(string $message): void
{
    var_dump($message);
}

function task1(): Generator
{
    yield VOsaka::defer(fn() => send('Deferred Task 1 executed'));
    yield var_dump('Start Task 1');
    yield from VOsaka::sleep(1);
    yield var_dump('Task 1 completed after 1 seconds');
}

function task2(): Generator
{
    yield VOsaka::defer(fn() => send('Deferred Task 2 executed'));
    yield var_dump('Start Task 2');
    yield from VOsaka::sleep(1);
    yield var_dump('Task 2 completed after 1 seconds');
}

VOsaka::join(
    task1(),
    task2()
);
VOsaka::run();
```

- Handle tasks with sleep, select

```php
use venndev\vosaka\VOsaka;

function workA(): Generator
{
    yield from VOsaka::sleep(1);
    yield var_dump('Work A completed after 1 second');
    return 'Work result';
}

function workB(): Generator
{
    yield from VOsaka::sleep(2);
    yield var_dump('Work B completed after 2 seconds');
    return 'Work result';
}

VOsaka::select(
    workA(),
    workB()
);
```

- Handle more advanced tasks.

```php
<?php

use venndev\vosaka\VOsaka;

// This is an example to apply to systems or software
// that have a similar structure to an event scheduling
//      repeater such as PocketMine-PMMP

function works(): void
{
    // This is a placeholder for the actual task you want to run.
    // For example, you might want to run a task every second.
    for ($i = 0; $i < 10000; $i++) {
        VOsaka::spawn(function (): Generator {
            yield from VOsaka::sleep(1); // Simulate a task that takes 1 second
            yield var_dump('Task executed at: ' . date('H:i:s'));
        });
    }
}

// Imagine this while(true) loop as a schedule repeater
//      that handles tasks per second or a certain batch.
// Call this bad because if there are too many asynchronous
//      tasks processing functions in the queue, it will cause
//      you to have to wait for asynchronous tasks to finish
//      processing before moving on to the next task.
function mainBad(): void
{
    // Imagine this while(true) loop as a schedule
    //      repeater that handles tasks per second or a certain batch.
    while (true) {
        works();

        // Yield control back to the event loop
        VOsaka::run();

        var_dump('Always run last. After 1000 tasks completed, this task will be released.');
    }
}

function mainGood(): void
{
    // Set the maximum number of tasks to run per period
    VOsaka::setMaximumPeriod(10);

    // Enable the maximum period limit
    VOsaka::setEnableMaximumPeriod(true);

    while (true) {
        works();

        // Yield control back to the event loop
        VOsaka::run();

        var_dump('After 10 tasks completed, this task will be released.');
    }
}

mainGood();
```

- Repeat task
```php
<?php
use venndev\vosaka\VOsaka;

function task1(): Generator
{
    yield VOsaka::defer(fn() => var_dump('Deferred Task 1 executed'));
    yield var_dump('Start Task 1');
    yield from VOsaka::sleep(1);
    yield var_dump('Task 1 completed after 1 seconds');
}

VOsaka::repeat(fn() => task1());
VOsaka::run();
```

- Handle tasks with Steam and Channel

```php
<?php
use venndev\vosaka\VChannel;
use venndev\vosaka\VOsaka;
use venndev\vosaka\VStream;

function main(): Generator
{
    $url = 'https://jsonplaceholder.typicode.com/albums/1';
    $data = new VChannel();
    yield VOsaka::defer(function (VChannel $data) {
        var_dump('Deferred: Data fetched from URL');
        foreach ($data->receive() as $chunk) {
            var_dump($chunk);
        }
        $data->close();
    }, $data);
    foreach (VStream::read($url) as $chunk) {
        yield $data->send($chunk);
    }
}

VOsaka::spawn(main());
VOsaka::run();
```

- Handle tasks with TraceHelper

```php
<?php

require '../vendor/autoload.php';

use venndev\vosaka\VTraceHelper;

// Initialize tracing with configuration
VTraceHelper::init([
    'max_traces' => 1000,
    'auto_flush' => true,
    'flush_threshold' => 50,
    'include_stack_trace' => true,
    'min_duration_ms' => 1,
    'output_format' => 'json',
    'output_file' => 'vosaka_traces.log',
    'memory_limit_mb' => 100
]);

// Example 1: Basic traced operations
function basicTracedExample(): Generator
{
    // This will be automatically traced
    yield from VTraceHelper::traceSleep(1, ['example' => 'basic', 'priority' => 'high']);

    // Add custom log entries
    VTraceHelper::log('Custom operation started', ['operation' => 'data_processing']);

    // Add custom tags
    VTraceHelper::tag(['user_id' => 12345, 'feature' => 'background_task']);

    yield from VTraceHelper::traceSleep(0.5, ['step' => 'processing']);

    VTraceHelper::log('Custom operation completed');

    return 'Basic example completed';
}

// Example 2: Traced generator with custom logic
function dataProcessingTask(int $iterations): Generator
{
    for ($i = 0; $i < $iterations; $i++) {
        VTraceHelper::log("Processing iteration {$i}", ['iteration' => $i]);

        // Simulate some work
        yield from VTraceHelper::traceSleep(0.1, ['iteration' => $i, 'type' => 'processing']);

        // Add progress tags
        VTraceHelper::tag(['progress' => round(($i + 1) / $iterations * 100, 2)]);
    }

    return "Processed {$iterations} iterations";
}

// Example 3: Error handling with tracing
function errorProneTask(): Generator
{
    try {
        VTraceHelper::log('Starting error-prone task');

        yield from VTraceHelper::traceSleep(0.5, ['stage' => 'preparation']);

        // Simulate random error
        if (rand(1, 3) === 1) {
            throw new Exception('Simulated error occurred');
        }

        yield from VTraceHelper::traceSleep(0.3, ['stage' => 'execution']);

        VTraceHelper::log('Task completed successfully');
        return 'Success';

    } catch (Exception $e) {
        VTraceHelper::log('Error occurred: ' . $e->getMessage(), ['level' => 'error']);
        throw $e;
    }
}

// Example 4: Complex traced workflow
function complexWorkflow(): Generator
{
    VTraceHelper::log('Starting complex workflow');

    // Step 1: Data preparation
    yield from VTraceHelper::trace(
        dataProcessingTask(5),
        'data_preparation',
        ['workflow_step' => 1, 'importance' => 'critical']
    );

    // Step 2: Parallel processing with join
    $task1 = VTraceHelper::trace(
        dataProcessingTask(3),
        'parallel_task_1',
        ['workflow_step' => 2, 'parallel_group' => 'A']
    );

    $task2 = VTraceHelper::trace(
        dataProcessingTask(3),
        'parallel_task_2',
        ['workflow_step' => 2, 'parallel_group' => 'A']
    );

    VTraceHelper::traceJoin(
        [$task1, $task2],
        ['parallel_task_1', 'parallel_task_2'],
        ['workflow_step' => 2, 'operation' => 'parallel_join']
    );

    // Step 3: Final processing
    yield from VTraceHelper::trace(
        dataProcessingTask(2),
        'final_processing',
        ['workflow_step' => 3, 'importance' => 'high']
    );

    VTraceHelper::log('Complex workflow completed');
    return 'Workflow completed successfully';
}

// Example 5: Using select with tracing
function selectExample(): Generator
{
    yield VTraceHelper::log('Starting select example');

    $fastTask = function(): Generator {
        yield from VTraceHelper::traceSleep(0.5, ['task_type' => 'fast']);
        return 'Fast task completed';
    };

    $slowTask = function(): Generator {
        yield from VTraceHelper::traceSleep(2, ['task_type' => 'slow']);
        return 'Slow task completed';
    };

    VTraceHelper::traceSelect(
        [$fastTask(), $slowTask()],
        ['fast_task', 'slow_task'],
        ['operation' => 'race_condition']
    );

    VTraceHelper::log('Select example completed');
}

// Example 6: Defer with tracing
function deferExample(): Generator
{
    VTraceHelper::log('Starting defer example');

    // Register deferred cleanup
    VTraceHelper::traceDefer(
        function() {
            VTraceHelper::log('Cleanup executed', ['level' => 'info']);
            // Cleanup code here
        },
        'cleanup_operation',
        ['priority' => 'high']
    );

    yield from VTraceHelper::traceSleep(1, ['main_operation' => true]);

    VTraceHelper::log('Main operation completed');
    return 'Defer example completed';
}

// Example 7: Spawn with tracing
function spawnExample(): Generator
{
    VTraceHelper::log('Starting spawn example');

    // Spawn background tasks
    for ($i = 0; $i < 3; $i++) {
        VTraceHelper::traceSpawn(
            backgroundTask($i),
            "background_task_{$i}",
            ['task_id' => $i, 'priority' => 'low']
        );
    }

    // Main task
    yield from VTraceHelper::traceSleep(1, ['main_task' => true]);

    VTraceHelper::log('Spawn example completed');
    return 'Spawned tasks running in background';
}

function backgroundTask(int $taskId): Generator
{
    $fn = function() use ($taskId): Generator {
        VTraceHelper::log("Background task {$taskId} started");
        yield from VTraceHelper::traceSleep(rand(1, 3), ['task_id' => $taskId]);
        VTraceHelper::log("Background task {$taskId} completed");
        return "Task {$taskId} result";
    };
    yield from VTraceHelper::trace(
        $fn(),
        "background_task_{$taskId}",
        ['background' => true, 'task_id' => $taskId]
    );
}

// Example 8: Performance monitoring
function performanceMonitoringExample(): Generator
{
    VTraceHelper::log('Starting performance monitoring example');

    $startTime = microtime(true);
    $startMemory = memory_get_usage(true);

    // Simulate CPU intensive task
    for ($i = 0; $i < 10; $i++) {
        yield from VTraceHelper::trace(
            cpuIntensiveTask($i),
            "cpu_task_{$i}",
            ['cpu_intensive' => true, 'iteration' => $i]
        );

        // Monitor memory usage
        $currentMemory = memory_get_usage(true);
        $memoryDelta = $currentMemory - $startMemory;

        VTraceHelper::tag([
            'memory_usage_bytes' => $currentMemory,
            'memory_delta_bytes' => $memoryDelta,
            'iteration' => $i
        ]);

        if ($memoryDelta > 10 * 1024 * 1024) { // 10MB threshold
            VTraceHelper::log('High memory usage detected', [
                'level' => 'warning',
                'memory_mb' => round($memoryDelta / 1024 / 1024, 2)
            ]);
        }
    }

    $totalTime = microtime(true) - $startTime;
    VTraceHelper::log('Performance monitoring completed', [
        'total_time_seconds' => round($totalTime, 3),
        'final_memory_mb' => round(memory_get_usage(true) / 1024 / 1024, 2)
    ]);

    return 'Performance monitoring completed';
}

function cpuIntensiveTask(int $iteration): Generator
{
    // Simulate CPU work
    $result = 0;
    for ($i = 0; $i < 100000; $i++) {
        $result += sqrt($i);
    }

    yield from VTraceHelper::traceSleep(0.1, ['computation_result' => $result]);

    return $result;
}

// Main execution function
function main(): Generator
{
    VTraceHelper::log('Application started', [
        'php_version' => PHP_VERSION,
        'memory_limit' => ini_get('memory_limit')
    ]);

    try {
        // Run examples
        echo "Running basic traced example...\n";
        $result1 = yield from VTraceHelper::traceAwait(basicTracedExample(), 'basic_example');
        echo "Result: {$result1}\n\n";

        echo "Running complex workflow...\n";
        $result2 = yield from VTraceHelper::traceAwait(complexWorkflow(), 'complex_workflow');
        echo "Result: {$result2}\n\n";

        echo "Running select example...\n";
        yield from selectExample();
        echo "Select example completed\n\n";

        echo "Running defer example...\n";
        $result3 = yield from VTraceHelper::traceAwait(deferExample(), 'defer_example');
        echo "Result: {$result3}\n\n";

        echo "Running spawn example...\n";
        $result4 = yield from VTraceHelper::traceAwait(spawnExample(), 'spawn_example');
        echo "Result: {$result4}\n\n";

        echo "Running performance monitoring...\n";
        $result5 = yield from VTraceHelper::traceAwait(performanceMonitoringExample(), 'performance_monitoring');
        echo "Result: {$result5}\n\n";

        // Error handling example
        echo "Running error-prone task...\n";
        try {
            $result6 = yield from VTraceHelper::traceAwait(errorProneTask(), 'error_prone_task');
            echo "Result: {$result6}\n\n";
        } catch (Exception $e) {
            echo "Caught error: {$e->getMessage()}\n\n";
        }

    } catch (Exception $e) {
        VTraceHelper::log('Application error: ' . $e->getMessage(), ['level' => 'error']);
        throw $e;
    }

    // Print statistics
    echo "=== Trace Statistics ===\n";
    $stats = VTraceHelper::getStats();
    foreach ($stats as $key => $value) {
        echo "{$key}: {$value}\n";
    }

    // Export traces
    echo "\n=== Exporting Traces ===\n";
    $jsonExport = VTraceHelper::export('json');
    file_put_contents('traces_export.json', $jsonExport);
    echo "JSON export saved to traces_export.json\n";

    $textExport = VTraceHelper::export('text');
    file_put_contents('traces_export.txt', $textExport);
    echo "Text export saved to traces_export.txt\n";

    VTraceHelper::log('Application completed successfully');

    return 'All examples completed';
}

// Run the main function
VTraceHelper::traceSpawn(main(), 'main_application', ['app_version' => '1.0.0']);

// Start the event loop with tracing
VTraceHelper::traceRun(['execution_mode' => 'traced']);

// Final flush and cleanup
echo "\n=== Final Trace Flush ===\n";
VTraceHelper::flush();
echo "Traces flushed to log file\n";

// Display final statistics
echo "\n=== Final Statistics ===\n";
$finalStats = VTraceHelper::getStats();
foreach ($finalStats as $key => $value) {
    echo "{$key}: {$value}\n";
}

VTraceHelper::clear();
echo "\nTrace system cleaned up\n";
```

- Socket

```php
<?php
use venndev\vosaka\VOsaka;
use venndev\vosaka\VSocket;

function simpleTcpExample(): Generator
{
    $socket = new VSocket('httpbin.org', 80, 'tcp', 30);
    
    $socket->on('connected', function($data) {
        echo $data . "\n";
    });
    
    $socket->on('data_received', function($data) {
        echo "Received: " . substr($data, 0, 100) . "...\n";
    });
    
    $socket->on('error', function($data) {
        echo "Error: " . $data . "\n";
    });
    
    try {
        yield from $socket->connect();
        
        $httpRequest = "GET /ip HTTP/1.1\r\nHost: httpbin.org\r\nConnection: close\r\n\r\n";
        yield from $socket->send($httpRequest);
        
        yield from $socket->handleTCP();
        
    } catch (Exception $e) {
        echo "Exception: " . $e->getMessage() . "\n";
    } finally {
        yield from $socket->disconnect();
    }
}

function multipleConnectionsExample(): Generator
{
    $sockets = [
        new VSocket('httpbin.org', 80, 'tcp'),
        new VSocket('jsonplaceholder.typicode.com', 80, 'tcp'),
        new VSocket('api.github.com', 443, 'tcp')
    ];
    
    foreach ($sockets as $i => $socket) {
        $socket->on('connected', function($data) use ($i) {
            echo "Socket {$i}: {$data}\n";
        });
        
        $socket->on('error', function($data) use ($i) {
            echo "Socket {$i} Error: {$data}\n";
        });
    }
    
    try {
        yield from VSocket::connectMultiple($sockets);
        echo "All sockets connected successfully!\n";
        
        $disconnectTasks = [];
        foreach ($sockets as $socket) {
            $disconnectTasks[] = $socket->disconnect();
        }
        yield VOsaka::join(...$disconnectTasks);
        
    } catch (Exception $e) {
        echo "Multiple connections error: " . $e->getMessage() . "\n";
    }
}

function autoReconnectExample(): Generator
{
    $socket = new VSocket('localhost', 9999, 'tcp');
    
    $socket->enableAutoReconnect(3, 1);
    
    $socket->on('reconnecting', function($data) {
        echo $data . "\n";
    });
    
    $socket->on('reconnect_failed', function($data) {
        echo $data . "\n";
    });
    
    $socket->on('connection_failed', function($data) {
        echo $data . "\n";
    });
    
    try {
        yield from $socket->connect();
    } catch (Exception $e) {
        echo "Final connection failure: " . $e->getMessage() . "\n";
    }
}

function socketRacingExample(): Generator
{
    $sockets = [
        new VSocket('httpbin.org', 80, 'tcp'),
        new VSocket('jsonplaceholder.typicode.com', 80, 'tcp'),
        new VSocket('api.github.com', 80, 'tcp')
    ];
    
    try {
        echo "Racing socket connections...\n";
        $winner = yield from VSocket::raceConnect($sockets);
        echo "Winner connected first!\n";
        
    } catch (Exception $e) {
        echo "Racing error: " . $e->getMessage() . "\n";
    }
}

function pingExample(): Generator
{
    $socket = new VSocket('httpbin.org', 80, 'tcp');
    
    $socket->on('ping_response', function($data) {
        echo "Ping response - Latency: {$data['latency']}ms\n";
    });
    
    $socket->on('ping_failed', function($data) {
        echo "Ping failed: {$data}\n";
    });
    
    try {
        yield from $socket->connect();
        
        for ($i = 0; $i < 5; $i++) {
            yield from $socket->ping();
            yield from VOsaka::sleep(1);
        }
        
    } catch (Exception $e) {
        echo "Ping example error: " . $e->getMessage() . "\n";
    } finally {
        yield from $socket->disconnect();
    }
}

function udpExample(): Generator
{
    $socket = new VSocket('8.8.8.8', 53, 'udp');
    
    $socket->on('connected', function($data) {
        echo "UDP: " . $data . "\n";
    });
    
    $socket->on('data_received', function($data) {
        echo "UDP received from {$data['peer']}: " . bin2hex($data['data']) . "\n";
    });
    
    try {
        yield from $socket->connect();
        
        $dnsQuery = "\x12\x34\x01\x00\x00\x01\x00\x00\x00\x00\x00\x00\x06google\x03com\x00\x00\x01\x00\x01";
        yield from $socket->send($dnsQuery);
        
        VOsaka::spawn(function() use ($socket): Generator {
            yield from VOsaka::sleep(5);
            yield from $socket->disconnect();
        });
        
        yield from $socket->handleUDP();
        
    } catch (Exception $e) {
        echo "UDP example error: " . $e->getMessage() . "\n";
    }
}

function socketPoolExample(): Generator
{
    $socketPool = [];
    
    for ($i = 0; $i < 5; $i++) {
        $socket = new VSocket('httpbin.org', 80, 'tcp');
        $socket->enableAutoReconnect(3, 1);
        
        $socket->on('connected', function($data) use ($i) {
            echo "Pool Socket {$i}: Connected\n";
        });
        
        $socketPool[] = $socket;
    }
    
    yield from VSocket::connectMultiple($socketPool);
    
    $tasks = [];
    foreach ($socketPool as $i => $socket) {
        $tasks[] = (function() use ($socket, $i): Generator {
            $request = "GET /delay/" . ($i + 1) . " HTTP/1.1\r\nHost: httpbin.org\r\nConnection: close\r\n\r\n";
            yield from $socket->send($request);
            
            $startTime = microtime(true);
            while ($socket->isConnected() && (microtime(true) - $startTime) < 10) {
                yield from VOsaka::sleep(0.1);
            }
            
            echo "Task {$i} completed\n";
        })();
    }
    
    yield VOsaka::join(...$tasks);
    
    $disconnectTasks = [];
    foreach ($socketPool as $socket) {
        $disconnectTasks[] = $socket->disconnect();
    }
    yield VOsaka::join(...$disconnectTasks);
}

function main(): Generator
{
    echo "=== VSocket with VOsaka Examples ===\n\n";
    
    echo "1. Simple TCP Example:\n";
    yield from simpleTcpExample();
    echo "\n";
    
    echo "2. Multiple Connections Example:\n";
    yield from multipleConnectionsExample();
    echo "\n";
    
    echo "3. Auto-reconnect Example:\n";
    yield from autoReconnectExample();
    echo "\n";
    
    echo "4. Socket Racing Example:\n";
    yield from socketRacingExample();
    echo "\n";
    
    echo "5. Ping Example:\n";
    yield from pingExample();
    echo "\n";
    
    echo "6. UDP Example:\n";
    yield from udpExample();
    echo "\n";
    
    echo "7. Socket Pool Example:\n";
    yield from socketPoolExample();
    echo "\n";
    
    echo "=== All examples completed ===\n";
}

VOsaka::spawn(main());
VOsaka::run();
```
=======
# vosaka
An asynchronous library for PHP
>>>>>>> 276f9edba5560db91f00002e951c22ea52cba0e4
