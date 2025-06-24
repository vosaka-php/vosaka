<?php

declare(strict_types=1);

namespace venndev\vosaka\runtime\metrics;

use venndev\vosaka\VOsaka;

final class MRuntime implements \venndev\vosaka\core\interfaces\Init
{
    public int $queueSize = 0;
    public int $runningTasks = 0;
    public int $chainedTasks = 0;
    public int $deferredTasks = 0;
    public int $droppedTasks = 0;
    public int $memoryUsage = 0;
    public int $peakMemory = 0;

    public static function init(): self
    {
        $instance = new self();
        $stats = VOsaka::getLoop()->getStats();
        $instance->queueSize = $stats['queue_size'] ?? 0;
        $instance->runningTasks = $stats['running_tasks'] ?? 0;
        $instance->chainedTasks = $stats['chained_tasks'] ?? 0;
        $instance->deferredTasks = $stats['deferred_tasks'] ?? 0;
        $instance->droppedTasks = $stats['dropped_tasks'] ?? 0;
        $instance->memoryUsage = $stats['memory_usage'] ?? 0;
        $instance->peakMemory = $stats['peak_memory'] ?? 0;
        return $instance;
    }
}