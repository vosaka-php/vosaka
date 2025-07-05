<?php

require __DIR__ . "/../vendor/autoload.php";

use venndev\vosaka\time\Sleep;
use venndev\vosaka\utils\Defer;
use venndev\vosaka\VOsaka;

function benchVosakaFastTasks()
{
    VOsaka::spawn(vosakaFastGenerator());
    VOsaka::run();
}

function vosakaFastGenerator(): Generator
{
    // $gate = new LoopGate(10);
    $results = [];
    for ($i = 0; $i < 10000; $i++) {
        if (count($results) < 1000) {
            $results[] = ["id" => $i, "result" => $i * 2];
        }
        yield;
        // if ($gate->tick()) {
        //     yield;
        // }
    }
    return $results;
}

benchVosakaFastTasks();
