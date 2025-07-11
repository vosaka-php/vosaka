<?php

require '../vendor/autoload.php';

use venndev\vosaka\VOsaka;
use venndev\vosaka\time\Sleep;

function work(int $id): Generator
{
  yield var_dump("Working on task $id");
  yield Sleep::new(1);
  yield var_dump("Task $id completed");
}

function main(): Generator
{
  $tasks = [1, 2, 3, 4, 5];

  $works = [];
  foreach ($tasks as $task) {
    $works[] = work($task);
  }

  yield from VOsaka::join(...$works)->unwrap();
}

VOsaka::spawn(main());
VOsaka::run();
