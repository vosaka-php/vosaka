<?php

declare(strict_types=1);

namespace venndev\vosaka\metrics;

use venndev\vosaka\core\interfaces\Init;

final class MTaskPool implements Init
{
    public static function init(): Init
    {
        return new self();
    }
}
