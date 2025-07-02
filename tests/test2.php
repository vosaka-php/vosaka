<?php

declare(strict_types=1);

require "../vendor/autoload.php";

use venndev\vosaka\time\Repeat;
use venndev\vosaka\time\Sleep;
use venndev\vosaka\VOsaka;

function main(): Generator
{
    $repeat = Repeat::new(function () {
        echo "Hello, world!\n";
    })(seconds: 1);

    // Run for 5 seconds
    yield Sleep::new(5);

    // Cancel the repeat task
    $repeat->cancel();

    echo "Task cancelled after 5 seconds.\n";
}

// Run the main function
VOsaka::spawn(main());
VOsaka::run();
