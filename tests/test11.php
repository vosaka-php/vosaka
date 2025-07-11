<?php

require '../vendor/autoload.php';

use venndev\vosaka\core\Future;
use venndev\vosaka\core\Result;
use venndev\vosaka\VOsaka;

function work(int $id): Result
{
    $fn = function () use ($id) {
        for ($i = 0; $i < $id; $i++) {
            yield $i;
        }
    };

    return Future::new($fn());
}

function main(): Generator
{
    foreach (work(5)->unwrap() as $value) {
        yield var_dump($value);
    }
}

VOsaka::spawn(main());
VOsaka::run();
