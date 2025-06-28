<?php

declare(strict_types=1);

namespace venndev\vosaka\runtime\metrics;

use venndev\vosaka\VOsaka;

final class MTaskPool implements \venndev\vosaka\core\interfaces\Init
{
    public int $poolSize = 0;
    public int $created = 0;
    public int $reused = 0;
    public float $reuseRate = 0.0;
    public int $deferredArrays = 0;
    public int $batchArrays = 0;

    public static function init(): self
    {
        $instance = new self();
        $loopStats = VOsaka::getLoop()->getStats();
        $taskPoolStats = $loopStats["task_pool_stats"] ?? [];
        $poolSizes = $loopStats["pool_sizes"] ?? [];

        $instance->poolSize = $taskPoolStats["pool_size"] ?? 0;
        $instance->created = $taskPoolStats["created"] ?? 0;
        $instance->reused = $taskPoolStats["reused"] ?? 0;
        $instance->reuseRate = $taskPoolStats["reuse_rate"] ?? 0.0;
        $instance->deferredArrays = $poolSizes["deferred_arrays"] ?? 0;
        $instance->batchArrays = $poolSizes["batch_arrays"] ?? 0;

        return $instance;
    }
}
