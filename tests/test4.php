<?php

use venndev\vosaka\time\Sleep;
use venndev\vosaka\VOsaka;

require "../vendor/autoload.php";

function work(int $id): Generator
{
    var_dump("Task $id started");
    yield Sleep::new(2); // Simulate a 2-second delay
    var_dump("Task $id completed after 2 seconds");
    return $id * 10; // Return some result based on the task ID
}

function main(): Generator
{
    var_dump("Starting main function...");
    yield from VOsaka::spawn(work(1))->unwrapOr("AAA");
    yield from VOsaka::spawn(work(2))();
    var_dump("All tasks completed");
}

VOsaka::spawn(main());
VOsaka::run();
