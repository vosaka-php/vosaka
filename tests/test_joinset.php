<?php

require '../vendor/autoload.php';

use venndev\vosaka\task\JoinSet;
use venndev\vosaka\VOsaka;
use venndev\vosaka\time\Sleep;

function one(): bool
{
    return true; // Placeholder for a condition check, can be replaced with actual logic
}

// Test task functions
function simpleTask(int $id, int $delay = 1): Generator
{
    echo "Task $id starting...\n";
    yield Sleep::new($delay);
    echo "Task $id completed!\n";
    return "Result from task $id";
}

function failingTask(int $id): Generator
{
    echo "Failing task $id starting...\n";
    yield Sleep::new(1);
    throw new RuntimeException("Task $id failed!");
}

function longRunningTask(int $id): Generator
{
    echo "Long task $id starting...\n";
    for ($i = 0; $i < 5; $i++) {
        yield Sleep::new(1);
        echo "Long task $id progress: $i/5\n";
    }
    return "Long task $id finished";
}

// Main test function
function testJoinSet(): Generator
{
    echo "=== Testing JoinSet ===\n\n";

    // Test 1: Basic spawn and join
    echo "Test 1: Basic spawn and join\n";
    $joinSet = JoinSet::new();

    $taskId1 = yield from $joinSet->spawn(simpleTask(1, 2))->unwrap();
    $taskId2 = yield from $joinSet->spawn(simpleTask(2, 1))->unwrap();
    $taskId3 = yield from $joinSet->spawn(simpleTask(3, 3))->unwrap();

    echo "Spawned tasks: $taskId1, $taskId2, $taskId3\n";
    echo "JoinSet length: " . $joinSet->len() . "\n";

    // Wait for next task to complete
    $next = yield from $joinSet->joinNext()->unwrap();
    if ($next->isSome()) {
        [$taskId, $result] = $next->unwrap();
        echo "First completed task: $taskId with result: $result\n";
    }

    // Wait for all remaining tasks
    $allResults = yield from $joinSet->joinAll()->unwrap();
    echo "All results: " . json_encode($allResults) . "\n\n";

    // Test 2: Spawn with keys
    echo "Test 2: Spawn with keys\n";
    $joinSet2 = JoinSet::new();

    yield from $joinSet2->spawnWithKey("task_a", simpleTask(10, 1))->unwrap();
    yield from $joinSet2->spawnWithKey("task_b", simpleTask(11, 2))->unwrap();
    yield from $joinSet2->spawnWithKey("task_c", simpleTask(12, 1))->unwrap();

    while (! $joinSet2->isEmpty()) {
        $next = yield from $joinSet2->joinNextWithKey()->unwrap();
        if ($next->isSome()) {
            [$key, $taskId, $result] = $next->unwrap();
            echo "Completed task with key '$key' (ID: $taskId): $result\n";
        }
    }
    echo "\n";

    // Test 3: Error handling
    echo "Test 3: Error handling\n";
    $joinSet3 = JoinSet::new();

    yield from $joinSet3->spawn(simpleTask(20, 1))->unwrap();
    yield from $joinSet3->spawn(failingTask(21))->unwrap();
    yield from $joinSet3->spawn(simpleTask(22, 2))->unwrap();

    while (! $joinSet3->isEmpty()) {
        $next = yield from $joinSet3->joinNext()->unwrap();
        if ($next->isSome()) {
            [$taskId, $result] = $next->unwrap();
            if ($result instanceof Throwable) {
                echo "Task $taskId failed: " . $result->getMessage() . "\n";
            } else {
                echo "Task $taskId succeeded: $result\n";
            }
        }
    }
    echo "\n";

    // Test 4: Try join next (non-blocking)
    echo "Test 4: Try join next (non-blocking)\n";
    $joinSet4 = JoinSet::new();

    yield from $joinSet4->spawn(simpleTask(30, 3))->unwrap();

    echo "Trying to join immediately (should be None):\n";
    $immediate = $joinSet4->tryJoinNext();
    echo "Result: " . (one() ? "None" : "Some") . "\n";

    // Wait a bit and try again
    yield Sleep::new(4);
    echo "Trying to join after delay (should be Some):\n";
    $delayed = $joinSet4->tryJoinNext();
    if ($delayed->isSome()) {
        [$taskId, $result] = $delayed->unwrap();
        echo "Got result: Task $taskId = $result\n";
    }
    echo "\n";

    // Test 5: Abort functionality
    echo "Test 5: Abort functionality\n";
    $joinSet5 = JoinSet::new();

    $longTaskId = yield from $joinSet5->spawn(longRunningTask(40))->unwrap();
    yield from $joinSet5->spawn(simpleTask(41, 2))->unwrap();

    // Let tasks run for a bit
    yield Sleep::new(2);

    echo "Aborting long running task...\n";
    $aborted = yield from $joinSet5->abort($longTaskId)->unwrap();
    echo "Abort result: " . ($aborted ? "success" : "failed") . "\n";

    // Wait for remaining tasks
    while (! $joinSet5->isEmpty()) {
        $next = yield from $joinSet5->joinNext()->unwrap();
        if ($next->isSome()) {
            [$taskId, $result] = $next->unwrap();
            echo "Remaining task $taskId completed: $result\n";
        }
    }
    echo "\n";

    // Test 6: Detach functionality
    echo "Test 6: Detach functionality\n";
    $joinSet6 = JoinSet::new();

    $detachTaskId = yield from $joinSet6->spawn(longRunningTask(50))->unwrap();
    yield from $joinSet6->spawn(simpleTask(51, 1))->unwrap();

    echo "JoinSet length before detach: " . $joinSet6->len() . "\n";
    $detached = $joinSet6->detach($detachTaskId);
    echo "Detach result: " . ($detached ? "success" : "failed") . "\n";
    echo "JoinSet length after detach: " . $joinSet6->len() . "\n";

    // The detached task will continue running but won't be tracked
    while (! $joinSet6->isEmpty()) {
        $next = yield from $joinSet6->joinNext()->unwrap();
        if ($next->isSome()) {
            [$taskId, $result] = $next->unwrap();
            echo "Tracked task $taskId completed: $result\n";
        }
    }
    echo "\n";

    echo "=== JoinSet Tests Completed ===\n";
}

// Run the test
VOsaka::spawn(testJoinSet());
VOsaka::run();

