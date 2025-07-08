<?php

require __DIR__ . "/../vendor/autoload.php";

use venndev\vosaka\time\Sleep;
use venndev\vosaka\utils\Defer;
use venndev\vosaka\VOsaka;

function work(): Generator
{
    yield Defer::new(function ($result) {
        var_dump("Deferred task executed with result:", $result);
    });
    yield var_dump("Starting work...");
    yield Sleep::new(1.0);
    return 10;
}

function main(): Generator
{
    yield from VOsaka::spawn(work())();
}

// Spawn main and await its completion
VOsaka::spawn(main());
VOsaka::run();
