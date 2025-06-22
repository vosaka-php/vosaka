<?php

declare(strict_types=1);

namespace venndev\vosaka\core;

use venndev\vosaka\utils\MemUtil;

class MemoryManager
{
    private float $memoryLimit; // MB
    private int $gcInterval; // Collect garbage every X tasks
    private int $taskCounter = 0;
    private float $lastMemoryUsage = 0;
    private int $memoryCheckCounter = 0;
    private float $baselineMemory = 0;
    private int $aggressiveGcCounter = 0;

    private const NORMAL_GC_THRESHOLD = 0.6;      // 60% - normal GC
    private const AGGRESSIVE_GC_THRESHOLD = 0.75; // 75% - aggressive GC
    private const CRITICAL_GC_THRESHOLD = 0.85;   // 85% - critical cleanup
    private const EMERGENCY_THRESHOLD = 0.95;     // 95% - emergency stop

    // Memory check frequencies
    private const NORMAL_CHECK_INTERVAL = 100;
    private const AGGRESSIVE_CHECK_INTERVAL = 25;
    private const CRITICAL_CHECK_INTERVAL = 10;

    public function __construct(float $memoryLimit = 64, int $gcInterval = 50)
    {
        $this->memoryLimit = $memoryLimit;
        $this->gcInterval = $gcInterval;
    }

    public function init(): void
    {
        gc_enable();
        gc_collect_cycles();

        $this->taskCounter = 0;
        $this->memoryCheckCounter = 0;
        $this->aggressiveGcCounter = 0;

        $this->baselineMemory = MemUtil::getMBUsed();
        $this->lastMemoryUsage = $this->baselineMemory;
    }

    public function checkMemoryUsage(): bool
    {
        $this->memoryCheckCounter++;
        $currentUsage = MemUtil::getMBUsed();
        $memoryPercentage = $currentUsage / $this->memoryLimit;

        $checkInterval = $this->getCheckInterval($memoryPercentage);

        if ($this->memoryCheckCounter % $checkInterval === 0) {
            if ($memoryPercentage > self::EMERGENCY_THRESHOLD) {
                $this->emergencyCleanup();
                $currentUsage = MemUtil::getMBUsed();

                if ($currentUsage / $this->memoryLimit > self::EMERGENCY_THRESHOLD) {
                    return false;
                }
            } elseif ($memoryPercentage > self::CRITICAL_GC_THRESHOLD) {
                $this->criticalCleanup();
                $currentUsage = MemUtil::getMBUsed();
            } elseif ($memoryPercentage > self::AGGRESSIVE_GC_THRESHOLD) {
                $this->aggressiveCleanup();
                $currentUsage = MemUtil::getMBUsed();
            } elseif ($memoryPercentage > self::NORMAL_GC_THRESHOLD) {
                $this->performGarbageCollection();
                $currentUsage = MemUtil::getMBUsed();
            }

            $this->lastMemoryUsage = $currentUsage;
        }

        return $this->lastMemoryUsage < $this->memoryLimit;
    }

    public function collectGarbage(): void
    {
        $this->taskCounter++;

        if ($this->taskCounter >= $this->gcInterval) {
            $memoryBefore = memory_get_usage(true);
            $this->performGarbageCollection();
            $memoryAfter = memory_get_usage(true);

            $memoryFreed = $memoryBefore - $memoryAfter;
            if ($memoryFreed < (1024 * 1024)) { // Less than 1MB freed
                $this->aggressiveGcCounter++;
                if ($this->aggressiveGcCounter >= 3) {
                    $this->aggressiveCleanup();
                    $this->aggressiveGcCounter = 0;
                }
            } else {
                $this->aggressiveGcCounter = 0;
            }

            $this->taskCounter = 0;
        }
    }

    public function forceGarbageCollection(): void
    {
        $this->performGarbageCollection();
        $this->taskCounter = 0;
        $this->aggressiveGcCounter = 0;
    }

    private function getCheckInterval(float $memoryPercentage): int
    {
        if ($memoryPercentage > self::CRITICAL_GC_THRESHOLD) {
            return self::CRITICAL_CHECK_INTERVAL;
        } elseif ($memoryPercentage > self::AGGRESSIVE_GC_THRESHOLD) {
            return self::AGGRESSIVE_CHECK_INTERVAL;
        }
        return self::NORMAL_CHECK_INTERVAL;
    }

    private function performGarbageCollection(): void
    {
        gc_collect_cycles();
        $this->lastMemoryUsage = MemUtil::getMBUsed();
    }

    private function aggressiveCleanup(): void
    {
        for ($i = 0; $i < 3; $i++) {
            gc_collect_cycles();
        }

        if (function_exists('gc_mem_caches')) {
            gc_mem_caches();
        }

        $this->lastMemoryUsage = MemUtil::getMBUsed();
    }

    private function criticalCleanup(): void
    {
        for ($i = 0; $i < 5; $i++) {
            gc_collect_cycles();
            if (function_exists('gc_mem_caches')) {
                gc_mem_caches();
            }
        }

        if (function_exists('memory_reset_peak_usage')) {
            memory_reset_peak_usage();
        }

        $this->lastMemoryUsage = MemUtil::getMBUsed();
    }

    private function emergencyCleanup(): void
    {
        for ($i = 0; $i < 10; $i++) {
            gc_collect_cycles();
            if (function_exists('gc_mem_caches')) {
                gc_mem_caches();
            }
        }

        if (function_exists('memory_reset_peak_usage')) {
            memory_reset_peak_usage();
        }

        $this->lastMemoryUsage = MemUtil::getMBUsed();
    }

    public function getMemoryLimit(): float
    {
        return $this->memoryLimit;
    }

    public function getMemoryPercentage(): float
    {
        return (MemUtil::getMBUsed() / $this->memoryLimit) * 100;
    }

    public function getBaselineMemory(): float
    {
        return $this->baselineMemory;
    }

    public function getMemoryGrowth(): float
    {
        return MemUtil::getMBUsed() - $this->baselineMemory;
    }

    public function isMemoryStable(): bool
    {
        $growth = $this->getMemoryGrowth();
        return $growth < 2.0; // Less than 2MB growth is considered stable
    }

    public function getDetailedStats(): array
    {
        $current = MemUtil::getMBUsed();
        $peak = MemUtil::getMBPeak();

        return [
            'current_usage' => $current,
            'peak_usage' => $peak,
            'baseline' => $this->baselineMemory,
            'growth' => $this->getMemoryGrowth(),
            'limit' => $this->memoryLimit,
            'percentage' => $this->getMemoryPercentage(),
            'is_stable' => $this->isMemoryStable(),
            'gc_counter' => $this->taskCounter,
            'aggressive_gc_counter' => $this->aggressiveGcCounter
        ];
    }
}