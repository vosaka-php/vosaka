---
sidebar_position: 2
---

# Basic Syntax

You have really come to how to use the generator syntax, then look through this code.

```php title="src/main.php"
<?php

require '../vendor/autoload.php';

use venndev\vosaka\time\Sleep;
use venndev\vosaka\utils\Defer;
use venndev\vosaka\VOsaka;

function work(): Generator
{
    yield Defer::c(function ($result) {
        var_dump('Deferred task executed with result:', $result);
    });
    yield var_dump('Starting work...');
    yield Sleep::c(1.0);
    return 10;
}

function main(): Generator
{
    yield from VOsaka::spawn(work())();
}

VOsaka::spawn(main());
VOsaka::run();
```

Similar to the asynchronous types of other languages, VOsaka uses `yield from` used to wait for an asynchronous job to complete and get the results. So `yield`, `yield` syntax allows you to spawn an asynchronous task that needs VOsaka to handle. The following `spawn` syntax allows you to create an asynchronous task.

`spawn` syntax will create an asynchronous and return task as a [Result](/classes/venndev/vosaka/core/Result.md)

`run` syntax will run VOsaka to process all asynchronous tasks.
