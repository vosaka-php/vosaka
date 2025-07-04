<?php

declare(strict_types=1);

namespace venndev\vosaka\utils;

use Generator;
use venndev\vosaka\core\Future;
use venndev\vosaka\core\Result;

trait FutureUtil
{
    public function createFuture(callable $generator): Result
    {
        return Future::new($generator());
    }
}
