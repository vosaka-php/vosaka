<?php

require '../vendor/autoload.php';

use venndev\vosaka\process\Command;
use venndev\vosaka\process\ProcOC;
use venndev\vosaka\VOsaka;

function main(): Generator
{
    // Create a command to echo "Hello, World!"
    $command = yield from Command::c('echo')
        ->arg('Hello, World!')
        ->spawn()
        ->expect("Command failed to spawn");

    // Don't worry about await() syntax you just need to 
    // understand that it will return a Result() if there 
    // is an error about spawn() it will have an error right above. 
    // But remember to have the unwrap() syntax or the syntax that 
    // can throw an exception when spawn() is above.
    $result = yield from $command->wait()->unwrapOr('Command failed');
    $result = ProcOC::clean($result);
    var_dump($result);
}

VOsaka::spawn(main());
VOsaka::run();