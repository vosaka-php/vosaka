<?php

require '../vendor/autoload.php';

use function venndev\vosaka\{run, spawn, defer};

function main(): Generator
{
    yield defer(function () {
        var_dump("Deferred execution completed.");
    });

    var_dump('Hello, world!');
}

spawn(main());
run();
