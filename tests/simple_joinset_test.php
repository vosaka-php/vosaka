<?php

require '../vendor/autoload.php';

use venndev\vosaka\task\JoinSet;
use venndev\vosaka\VOsaka;
use venndev\vosaka\time\Sleep;

// Simple test task
function testTask(int $id, int $delay = 1): Generator
{
    echo "Task $id starting (delay: {$delay}s)...\n";
    yield from Sleep::new($delay)->toGenerator();
    echo "Task $id completed!\n";
    return "Result from task $id";
}

// Main test
function simpleJoinSetTest(): Generator
{
    echo "=== Simple JoinSet Test ===\n";

    $joinSet = JoinSet::new();
    echo "Created JoinSet\n";

    // Spawn some tasks
    $taskId1 = yield from $joinSet->spawn(testTask(1, 1))->unwrap();
    $taskId2 = yield from $joinSet->spawn(testTask(2, 2))->unwrap();
    $taskId3 = yield from $joinSet->spawn(testTask(3, 1))->unwrap();

    echo "Spawned 3 tasks: $taskId1, $taskId2, $taskId3\n";
    echo "JoinSet length: ".$joinSet->len()."\n";

    // Wait for tasks to complete one by one
    while (! $joinSet->isEmpty()) {
        echo "Waiting for next task...\n";
        $next = yield from $joinSet->joinNext()->unwrap();

        if ($next->isSome()) {
            [$taskId, $result] = $next->unwrap();
            echo "Task $taskId completed with result: $result\n";
        }
    }

    echo "All tasks completed!\n";
    echo "JoinSet is empty: ".($joinSet->isEmpty() ? "true" : "false")."\n";
}

// Run the test
VOsaka::spawn(simpleJoinSetTest());
VOsaka::run();