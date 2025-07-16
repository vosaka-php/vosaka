<?php

require '../vendor/autoload.php';

use venndev\vosaka\task\Loopify;
use venndev\vosaka\VOsaka;

function main(): Generator
{
    $loop = Loopify::new(function () {
        var_dump("Hello, World!");
        return 0;
    })
        ->map(function ($value) {
            return $value + 1;
        })
        ->map(function ($value) {
            var_dump("Data: " . $value);
        });

    yield from $loop->wait()->unwrap();
}

VOsaka::spawn(main());
VOsaka::run();
