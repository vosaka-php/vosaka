<?php

declare(strict_types=1);

namespace venndev\vosaka\metrics;

use venndev\vosaka\VOsaka;

final class MTaskPool implements \venndev\vosaka\core\interfaces\Init
{
    public int $poolSize = 0;
    public int $created = 0;
    public int $reused = 0;
    public float $reuseRate = 0.0;

    public static function init(): self
    {
        $instance = new self();
        $stats = VOsaka::getLoop()->getStats()['task_pool_stats'] ?? [];
        $instance->poolSize = $stats['pool_size'] ?? 0;
        $instance->created = $stats['created'] ?? 0;
        $instance->reused = $stats['reused'] ?? 0;
        $instance->reuseRate = $stats['reuse_rate'] ?? 0.0;
        return $instance;
    }
}