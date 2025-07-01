<?php

declare(strict_types=1);

namespace venndev\vosaka\runtime\metrics;

use venndev\vosaka\core\interfaces\Init;

final class MRuntime implements Init
{
    public static function init(): Init
    {
        return new self();
    }
}
