<?php

declare(strict_types=1);

namespace venndev\vosaka\runtime\metrics;

use venndev\vosaka\VOsaka;

final class MRuntime implements \venndev\vosaka\core\interfaces\Init
{
    public int $queueSize = 0;
    public int $runningTasks = 0;
    public int $batchSize = 0;
    public int $cycleTaskCount = 0;
    public int $memoryCheckInterval = 0;
    public int $deferredArrays = 0;
    public int $batchArrays = 0;
    public int $deferredTasks = 0;
    public int $droppedTasks = 0;
    public int $memoryUsage = 0;
    public int $peakMemory = 0;

    public static function init(): self
    {
        $instance = new self();
        $stats = VOsaka::getLoop()->getStats();
        $poolSizes = $stats["pool_sizes"] ?? [];

        $instance->queueSize = $stats["queue_size"] ?? 0;
        $instance->runningTasks = $stats["running_tasks"] ?? 0;
        $instance->deferredTasks = $stats["deferred_tasks"] ?? 0;
        $instance->droppedTasks = $stats["dropped_tasks"] ?? 0;
        $instance->memoryUsage = $stats["memory_usage"] ?? 0;
        $instance->peakMemory = $stats["peak_memory"] ?? 0;
        $instance->batchSize = $stats["batch_size"] ?? 0;
        $instance->cycleTaskCount = $stats["cycle_task_count"] ?? 0;
        $instance->memoryCheckInterval = $stats["memory_check_interval"] ?? 0;
        $instance->deferredArrays = $poolSizes["deferred_arrays"] ?? 0;
        $instance->batchArrays = $poolSizes["batch_arrays"] ?? 0;

        return $instance;
    }
}
