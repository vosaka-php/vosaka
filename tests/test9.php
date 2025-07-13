
<?php
require __DIR__ . "/../vendor/autoload.php";

use venndev\vosaka\time\Sleep;
use venndev\vosaka\core\Defer;
use venndev\vosaka\VOsaka;

function work(int $id): Generator
{
    yield Defer::new(function ($result) use ($id) {
        var_dump("Deferred task {$id} executed with result:", $result);
    });
    yield var_dump("Starting work {$id} ...");
    yield Sleep::new(1.0);
    return 10;
}

function main(): Generator
{
    for ($i = 1; $i <= 1000; $i++) {
        VOsaka::spawn(work($i))();
        yield;
    }
}

$time = microtime(true);

// Spawn main and await its completion
VOsaka::spawn(main());
VOsaka::run();

$time = microtime(true) - $time;
var_dump("Total time taken: {$time} seconds");

