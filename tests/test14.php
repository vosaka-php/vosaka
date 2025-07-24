<?php

use venndev\vosaka\time\Sleep;
use venndev\vosaka\VOsaka;

require '../vendor/autoload.php';

function work(): Generator
{
    yield Sleep::new(1.0);
    return 10;
}

function main(): Generator
{
    $works = [];
    for ($i = 0; $i < 5; $i++) {
        $works[] = work();
    }

    $results = yield from VOsaka::join(...$works)->unwrap();
    foreach ($results as $result) {
        var_dump($result);
    }
}

VOsaka::spawn(main());
VOsaka::run();
