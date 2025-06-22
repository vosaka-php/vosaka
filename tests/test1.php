<?php

require '../vendor/autoload.php';

use venndev\vosaka\eventloop\scheduler\Defer;
use venndev\vosaka\io\Await;
use venndev\vosaka\time\Sleep;
use venndev\vosaka\VOsaka;

function work(): Generator
{
    yield Defer::c(function () {
        var_dump('Deferred task executed.');
    });
    yield var_dump('Starting work...');
    yield Sleep::c(1.0);
    return 'Finished work after 1 second.';
}

function main(): Generator
{
    $result = yield from Await::c(work())();
    var_dump($result);
}

VOsaka::spawn(main());
VOsaka::run();
