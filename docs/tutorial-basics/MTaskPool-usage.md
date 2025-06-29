# MTaskPool Usage Guide

This guide demonstrates how to use the enhanced MTaskPool metrics system with its advanced performance optimizations and monitoring capabilities.

## Basic Usage

### Simple Metrics Collection

```php
<?php

use venndev\vosaka\runtime\metrics\MTaskPool;
use venndev\vosaka\VOsaka;

// Initialize the event loop
$loop = VOsaka::getLoop();

// Spawn some tasks to generate metrics
for ($i = 0; $i < 100; $i++) {
    $loop->spawn(function() {
        yield new \venndev\vosaka\time\Sleep(0.01);
        return "Task completed";
    });
}

// Collect basic metrics
$metrics = MTaskPool::init();

echo "Pool Size: " . $metrics->poolSize . "\n";
echo "Created: " . $metrics->created . "\n";
echo "Reused: " . $metrics->reused . "\n";
echo "Reuse Rate: " . number_format($metrics->reuseRate, 2) . "%\n";
echo "Deferred Arrays: " . $metrics->deferredArrays . "\n";
echo "Batch Arrays: " . $metrics->batchArrays . "\n";
```

## Enhanced Metrics

### Comprehensive Statistics

```php
<?php

use venndev\vosaka\runtime\metrics\MTaskPool;

// Get detailed statistics
$metrics = MTaskPool::init();
$detailedStats = $metrics->getDetailedStats();

// Core metrics
echo "=== Core Metrics ===\n";
foreach ($detailedStats['core'] as $key => $value) {
    echo ucfirst(str_replace('_', ' ', $key)) . ": " . $value . "\n";
}

// Efficiency metrics
echo "\n=== Efficiency Metrics ===\n";
foreach ($detailedStats['efficiency'] as $key => $value) {
    if (is_float($value)) {
        echo ucfirst(str_replace('_', ' ', $key)) . ": " . number_format($value, 2) . "%\n";
    } else {
        echo ucfirst(str_replace('_', ' ', $key)) . ": " . $value . "\n";
    }
}

// Memory metrics
echo "\n=== Memory Metrics ===\n";
foreach ($detailedStats['memory'] as $key => $value) {
    if ($key === 'pool_memory_usage' || $key === 'peak_pool_memory') {
        echo ucfirst(str_replace('_', ' ', $key)) . ": " . number_format($value / 1024, 2) . " KB\n";
    } else {
        echo ucfirst(str_replace('_', ' ', $key)) . ": " . number_format($value, 2) . "%\n";
    }
}

// Performance metrics
echo "\n=== Performance Metrics ===\n";
foreach ($detailedStats['performance'] as $key => $value) {
    if (strpos($key, 'time') !== false) {
        echo ucfirst(str_replace('_', ' ', $key)) . ": " . number_format($value * 1000, 3) . " ms\n";
    } elseif (is_bool($value)) {
        echo ucfirst(str_replace('_', ' ', $key)) . ": " . ($value ? 'Yes' : 'No') . "\n";
    } else {
        echo ucfirst(str_replace('_', ' ', $key)) . ": " . $value . "\n";
    }
}
```

## Performance Optimization

### High-Performance Mode

```php
<?php

use venndev\vosaka\runtime\metrics\MTaskPool;
use venndev\vosaka\VOsaka;

// Enable high-performance mode for maximum throughput
MTaskPool::enableHighPerformanceMode();

$loop = VOsaka::getLoop();
$loop->enableHighPerformanceMode();

// Spawn many tasks to test high-throughput scenario
for ($i = 0; $i < 10000; $i++) {
    $loop->spawn(function() use ($i) {
        // Simulate some work
        $result = $i * 2;
        yield new \venndev\vosaka\time\Sleep(0.001);
        return $result;
    });
}

// Run with performance monitoring
$startTime = microtime(true);
$loop->run();
$endTime = microtime(true);

// Collect performance metrics
$metrics = MTaskPool::init();
$stats = $metrics->getDetailedStats();

echo "=== High-Performance Results ===\n";
echo "Execution Time: " . number_format(($endTime - $startTime) * 1000, 2) . " ms\n";
echo "Tasks Processed: 10000\n";
echo "Pool Efficiency: " . number_format($stats['efficiency']['pool_efficiency'], 2) . "%\n";
echo "Hit Rate: " . number_format($stats['efficiency']['hit_rate'], 2) . "%\n";
echo "Fast Path Hits: " . $stats['performance']['fast_path_hits'] . "\n";
echo "In Hot Path: " . ($stats['performance']['in_hot_path'] ? 'Yes' : 'No') . "\n";
```

### Memory-Conservative Mode

```php
<?php

use venndev\vosaka\runtime\metrics\MTaskPool;
use venndev\vosaka\VOsaka;

// Enable memory-conservative mode for resource-constrained environments
MTaskPool::enableMemoryConservativeMode();

$loop = VOsaka::getLoop();
$loop->enableMemoryConservativeMode();

// Spawn moderate number of tasks
for ($i = 0; $i < 1000; $i++) {
    $loop->spawn(function() use ($i) {
        yield new \venndev\vosaka\time\Sleep(0.01);
        return "Result: " . $i;
    });
}

$loop->run();

// Monitor memory usage
$metrics = MTaskPool::init();
$stats = $metrics->getDetailedStats();

echo "=== Memory-Conservative Results ===\n";
echo "Pool Memory Usage: " . number_format($stats['memory']['pool_memory_usage'] / 1024, 2) . " KB\n";
echo "Memory Efficiency: " . number_format($stats['memory']['memory_efficiency'], 2) . "%\n";
echo "Peak Pool Memory: " . number_format($stats['memory']['peak_pool_memory'] / 1024, 2) . " KB\n";
```

## Cache Management

### Cache Statistics and Control

```php
<?php

use venndev\vosaka\runtime\metrics\MTaskPool;

// Get cache statistics
$cacheStats = MTaskPool::getCacheStats();

echo "=== Cache Statistics ===\n";
echo "Cached Instance Exists: " . ($cacheStats['cached_instance_exists'] ? 'Yes' : 'No') . "\n";
echo "Cache Invalidation Counter: " . $cacheStats['cache_invalidation_counter'] . "\n";
echo "Cache Refresh Interval: " . $cacheStats['cache_refresh_interval'] . "\n";
echo "Time Since Refresh: " . number_format($cacheStats['time_since_refresh'] * 1000, 2) . " ms\n";
echo "In Hot Path: " . ($cacheStats['in_hot_path'] ? 'Yes' : 'No') . "\n";
echo "Instance Pool Size: " . $cacheStats['instance_pool_size'] . "/" . $cacheStats['max_pool_size'] . "\n";

// Force cache refresh when needed
if ($cacheStats['time_since_refresh'] > 1.0) { // More than 1 second old
    echo "\nForcing cache refresh...\n";
    $freshMetrics = MTaskPool::forceRefresh();
    echo "Fresh metrics collected!\n";
}
```

## Real-time Monitoring

### Continuous Monitoring Loop

```php
<?php

use venndev\vosaka\runtime\metrics\MTaskPool;
use venndev\vosaka\VOsaka;

function monitorTaskPool(): void {
    $loop = VOsaka::getLoop();
    
    // Spawn background monitoring task
    $loop->spawn(function() {
        while (true) {
            $metrics = MTaskPool::init();
            $stats = $metrics->getDetailedStats();
            
            // Clear screen (works on most terminals)
            echo "\033[2J\033[H";
            
            echo "=== VOsaka Task Pool Monitor ===\n";
            echo "Time: " . date('Y-m-d H:i:s') . "\n\n";
            
            // Key performance indicators
            echo "Pool Efficiency: " . number_format($stats['efficiency']['pool_efficiency'], 1) . "%\n";
            echo "Hit Rate: " . number_format($stats['efficiency']['hit_rate'], 1) . "%\n";
            echo "Active Objects: " . $stats['efficiency']['active_objects'] . "\n";
            echo "Available Objects: " . $stats['efficiency']['available_objects'] . "\n";
            
            // Performance status
            $inHotPath = $stats['performance']['in_hot_path'];
            echo "Performance Mode: " . ($inHotPath ? 'HOT PATH' : 'Normal') . "\n";
            
            if ($inHotPath) {
                echo "Hot Cycles: " . $stats['performance']['consecutive_high_load_cycles'] . "\n";
            }
            
            // Memory usage
            $memUsage = $stats['memory']['pool_memory_usage'] / 1024;
            echo "Pool Memory: " . number_format($memUsage, 1) . " KB\n";
            
            // Cache performance
            $cacheStats = MTaskPool::getCacheStats();
            echo "Cache Hits: " . $cacheStats['cache_invalidation_counter'] . "\n";
            
            yield new \venndev\vosaka\time\Sleep(1.0); // Update every second
        }
    });
}

// Start monitoring
monitorTaskPool();

// Spawn some work to monitor
$loop = VOsaka::getLoop();
for ($i = 0; $i < 50; $i++) {
    $loop->spawn(function() use ($i) {
        yield new \venndev\vosaka\time\Sleep(rand(10, 100) / 1000); // 10-100ms
        return "Task $i completed";
    });
}

$loop->run();
```

## Advanced Usage

### Custom Performance Analysis

```php
<?php

use venndev\vosaka\runtime\metrics\MTaskPool;
use venndev\vosaka\VOsaka;

function analyzePoolPerformance(): array {
    $measurements = [];
    
    // Collect baseline
    $baseline = MTaskPool::forceRefresh();
    $measurements['baseline'] = $baseline->getDetailedStats();
    
    // Run test workload
    $loop = VOsaka::getLoop();
    
    $startTime = microtime(true);
    for ($i = 0; $i < 5000; $i++) {
        $loop->spawn(function() use ($i) {
            // Mix of different task types
            if ($i % 3 === 0) {
                yield new \venndev\vosaka\time\Sleep(0.001);
            } elseif ($i % 3 === 1) {
                yield new \venndev\vosaka\time\Sleep(0.005);
            } else {
                yield new \venndev\vosaka\time\Sleep(0.010);
            }
            return $i;
        });
    }
    
    $loop->run();
    $executionTime = microtime(true) - $startTime;
    
    // Collect final metrics
    $final = MTaskPool::forceRefresh();
    $measurements['final'] = $final->getDetailedStats();
    $measurements['execution_time'] = $executionTime;
    
    return $measurements;
}

function reportPerformanceAnalysis(array $measurements): void {
    $baseline = $measurements['baseline'];
    $final = $measurements['final'];
    $execTime = $measurements['execution_time'];
    
    echo "=== Performance Analysis Report ===\n\n";
    
    // Task throughput
    $tasksProcessed = $final['core']['created'] - $baseline['core']['created'];
    $throughput = $tasksProcessed / $execTime;
    
    echo "Tasks Processed: " . number_format($tasksProcessed) . "\n";
    echo "Execution Time: " . number_format($execTime * 1000, 2) . " ms\n";
    echo "Throughput: " . number_format($throughput) . " tasks/second\n\n";
    
    // Pool efficiency improvements
    $efficiencyImprovement = $final['efficiency']['pool_efficiency'] - $baseline['efficiency']['pool_efficiency'];
    echo "Pool Efficiency Change: " . number_format($efficiencyImprovement, 2) . "%\n";
    
    $hitRateImprovement = $final['efficiency']['hit_rate'] - $baseline['efficiency']['hit_rate'];
    echo "Hit Rate Change: " . number_format($hitRateImprovement, 2) . "%\n\n";
    
    // Memory usage
    echo "Memory Usage: " . number_format($final['memory']['pool_memory_usage'] / 1024, 2) . " KB\n";
    echo "Memory Efficiency: " . number_format($final['memory']['memory_efficiency'], 2) . "%\n\n";
    
    // Performance path analysis
    $totalPathHits = $final['performance']['fast_path_hits'] + $final['performance']['slow_path_hits'];
    if ($totalPathHits > 0) {
        $fastPathPercentage = ($final['performance']['fast_path_hits'] / $totalPathHits) * 100;
        echo "Fast Path Usage: " . number_format($fastPathPercentage, 1) . "%\n";
    }
    
    if ($final['performance']['in_hot_path']) {
        echo "Hot Path Optimizations: ACTIVE\n";
        echo "Consecutive Hot Cycles: " . $final['performance']['consecutive_high_load_cycles'] . "\n";
    }
}

// Run analysis
$results = analyzePoolPerformance();
reportPerformanceAnalysis($results);

// Reset state for clean testing
MTaskPool::resetStaticState();
```

## Best Practices

### 1. Cache Management
- Use `forceRefresh()` sparingly, only when real-time accuracy is critical
- Monitor cache hit rates to ensure optimal performance
- Consider cache validity in high-frequency monitoring scenarios

### 2. Performance Modes
- Use `enableHighPerformanceMode()` for throughput-critical applications
- Use `enableMemoryConservativeMode()` in resource-constrained environments
- Monitor system resources when switching between modes

### 3. Metric Collection Frequency
- Balance monitoring frequency with performance overhead
- Use hot path detection to automatically optimize collection
- Implement custom monitoring intervals based on application needs

### 4. Error Handling
- The metrics system includes graceful error handling
- Monitor for collection errors in production environments
- Use cached values as fallbacks when collection fails

### 5. Memory Management
- Object pooling reduces garbage collection pressure
- Monitor pool sizes and adjust limits based on usage patterns
- Use `resetStaticState()` for clean testing and benchmarking

## Troubleshooting

### Common Issues

**High Memory Usage**
```php
// Check memory efficiency
$metrics = MTaskPool::init();
$stats = $metrics->getDetailedStats();

if ($stats['memory']['memory_efficiency'] < 50.0) {
    echo "Warning: Low memory efficiency detected\n";
    echo "Consider enabling memory conservative mode\n";
    MTaskPool::enableMemoryConservativeMode();
}
```

**Low Pool Efficiency**
```php
// Analyze pool utilization
$metrics = MTaskPool::init();
if ($metrics->poolEfficiency < 70.0) {
    echo "Warning: Low pool efficiency (" . $metrics->poolEfficiency . "%)\n";
    echo "Pool Hits: " . $metrics->poolHits . "\n";
    echo "Pool Misses: " . $metrics->poolMisses . "\n";
    echo "Consider adjusting pool sizes or task patterns\n";
}
```

**Cache Performance Issues**
```php
// Check cache statistics
$cacheStats = MTaskPool::getCacheStats();
if ($cacheStats['time_since_refresh'] > 5.0) {
    echo "Warning: Cache is stale\n";
    MTaskPool::forceRefresh();
}
```

This comprehensive guide demonstrates the enhanced capabilities of MTaskPool, showing how to leverage its advanced features for optimal performance monitoring and system optimization.