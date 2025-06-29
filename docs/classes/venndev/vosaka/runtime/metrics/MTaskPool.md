***

# MTaskPool

MTaskPool - Enhanced Task Pool Metrics with Performance Optimizations

This class provides comprehensive metrics for the EventLoop's task pool system,
following the same performance patterns as EventLoop with:

- Smart Caching: Reduces expensive stats collection calls
- Batch Updates: Groups metric updates for efficiency
- Memory Pooling: Reuses metric objects to minimize allocations
- Performance Monitoring: Tracks efficiency and optimization opportunities
- Adaptive Refresh: Adjusts refresh rates based on system load
- Hot Path Detection: Optimizes metric collection during high throughput

Architecture:
1. Cached Metrics: Avoids expensive stat collection on every access
2. Batch Processing: Updates multiple metrics in single operations
3. Object Pooling: Reuses metric instances for zero-allocation updates
4. Performance Tracking: Monitors metric collection overhead
5. Adaptive Behavior: Adjusts collection frequency based on load

* Full name: `\venndev\vosaka\runtime\metrics\MTaskPool`
* This class is marked as **final** and can't be subclassed
* This class implements:
[`\venndev\vosaka\core\interfaces\Init`](../../core/interfaces/Init.md)
* This class is a **Final class**


## Constants

| Constant | Visibility | Type | Value |
|:---------|:-----------|:-----|:------|
|`CACHE_INVALIDATION_FREQUENCY`|private| |25|
|`MEMORY_CHECK_BATCH`|private| |20|
|`FAST_PATH_THRESHOLD`|private| |100|
|`PERFORMANCE_SAMPLE_SIZE`|private| |1000|

## Properties


### poolSize



```php
public int $poolSize
```






***

### created



```php
public int $created
```






***

### reused



```php
public int $reused
```






***

### reuseRate



```php
public float $reuseRate
```






***

### deferredArrays



```php
public int $deferredArrays
```






***

### batchArrays



```php
public int $batchArrays
```






***

### activeObjects



```php
public int $activeObjects
```






***

### availableObjects



```php
public int $availableObjects
```






***

### totalAllocations



```php
public int $totalAllocations
```






***

### totalDeallocations



```php
public int $totalDeallocations
```






***

### poolEfficiency



```php
public float $poolEfficiency
```






***

### poolHits



```php
public int $poolHits
```






***

### poolMisses



```php
public int $poolMisses
```






***

### hitRate



```php
public float $hitRate
```






***

### poolMemoryUsage



```php
public int $poolMemoryUsage
```






***

### peakPoolMemory



```php
public int $peakPoolMemory
```






***

### memoryEfficiency



```php
public float $memoryEfficiency
```






***

### fastPathHits



```php
public int $fastPathHits
```






***

### slowPathHits



```php
public int $slowPathHits
```






***

### averageRetrievalTime



```php
public float $averageRetrievalTime
```






***

### averageReturnTime



```php
public float $averageReturnTime
```






***

### cachedInstance



```php
private static ?self $cachedInstance
```



* This property is **static**.


***

### cacheInvalidationCounter



```php
private static int $cacheInvalidationCounter
```



* This property is **static**.


***

### cacheRefreshInterval



```php
private static int $cacheRefreshInterval
```



* This property is **static**.


***

### lastRefreshTime



```php
private static float $lastRefreshTime
```



* This property is **static**.


***

### inHotPath



```php
private static bool $inHotPath
```



* This property is **static**.


***

### consecutiveHighLoadCycles



```php
private static int $consecutiveHighLoadCycles
```



* This property is **static**.


***

### hotPathThreshold



```php
private static int $hotPathThreshold
```



* This property is **static**.


***

### pendingUpdates



```php
private static array $pendingUpdates
```



* This property is **static**.


***

### batchUpdateSize



```php
private static int $batchUpdateSize
```



* This property is **static**.


***

### batchUpdateEnabled



```php
private static bool $batchUpdateEnabled
```



* This property is **static**.


***

### instancePool



```php
private static array $instancePool
```



* This property is **static**.


***

### maxPoolSize



```php
private static int $maxPoolSize
```



* This property is **static**.


***

### poolGrowthSize



```php
private static int $poolGrowthSize
```



* This property is **static**.


***

## Methods


### init

Initialize MTaskPool with enhanced caching and performance optimizations

```php
public static init(): self
```



* This method is **static**.





**Return Value:**

Optimized metrics instance with current statistics




***

### getPooledInstance

Get a pooled instance to minimize allocations

```php
private static getPooledInstance(): self
```



* This method is **static**.





**Return Value:**

Reused or new instance from object pool




***

### returnPooledInstance

Return instance to pool for reuse

```php
private static returnPooledInstance(self $instance): void
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$instance` | **self** | Instance to return to pool |





***

### shouldUseCachedInstance

Check if cached instance should be used (following EventLoop caching patterns)

```php
private static shouldUseCachedInstance(): bool
```



* This method is **static**.





**Return Value:**

True if cached instance is valid




***

### getCachedInstance

Get cached instance with validation

```php
private static getCachedInstance(): self
```



* This method is **static**.





**Return Value:**

Cached metrics instance




***

### updateCache

Update cache with new instance

```php
private static updateCache(self $instance): void
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$instance` | **self** | New instance to cache |





***

### detectHotPath

Detect hot path conditions and adjust performance settings

```php
private static detectHotPath(): void
```



* This method is **static**.








***

### enableHotPathOptimizations

Enable optimizations for high-throughput scenarios

```php
private static enableHotPathOptimizations(): void
```



* This method is **static**.








***

### disableHotPathOptimizations

Disable hot path optimizations and return to normal operation

```php
private static disableHotPathOptimizations(): void
```



* This method is **static**.








***

### reset

Reset instance to clean state for pooling

```php
private reset(): void
```












***

### collectMetrics

Collect comprehensive metrics from EventLoop with performance optimizations

```php
private collectMetrics(): void
```












***

### extractCoreMetrics

Extract core metrics from loop statistics

```php
private extractCoreMetrics(array $loopStats): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$loopStats` | **array** | EventLoop statistics |





***

### extractEnhancedMetrics

Extract enhanced metrics following EventLoop patterns

```php
private extractEnhancedMetrics(array $loopStats): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$loopStats` | **array** | EventLoop statistics |





***

### calculatePerformanceMetrics

Calculate performance metrics

```php
private calculatePerformanceMetrics(array $loopStats): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$loopStats` | **array** | EventLoop statistics |





***

### calculateMemoryMetrics

Calculate memory efficiency metrics

```php
private calculateMemoryMetrics(array $loopStats): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$loopStats` | **array** | EventLoop statistics |





***

### handleMetricsCollectionError

Handle metrics collection errors gracefully

```php
private handleMetricsCollectionError(\Throwable $error): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$error` | **\Throwable** | Collection error |





***

### updateCollectionPerformance

Update collection performance tracking

```php
private updateCollectionPerformance(float $collectionTime): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$collectionTime` | **float** | Time taken to collect metrics |





***

### getDetailedStats

Get comprehensive statistics including performance metrics

```php
public getDetailedStats(): array
```









**Return Value:**

Detailed metrics and performance statistics




***

### enableHighPerformanceMode

Enable high-performance mode for metrics collection

```php
public static enableHighPerformanceMode(): void
```



* This method is **static**.








***

### enableMemoryConservativeMode

Enable memory-conservative mode for metrics collection

```php
public static enableMemoryConservativeMode(): void
```



* This method is **static**.








***

### resetStaticState

Reset all static state (useful for testing)

```php
public static resetStaticState(): void
```



* This method is **static**.








***

### forceRefresh

Force cache invalidation and refresh

```php
public static forceRefresh(): self
```



* This method is **static**.








***

### getCacheStats

Get current cache statistics

```php
public static getCacheStats(): array
```



* This method is **static**.





**Return Value:**

Cache performance metrics




***


***
> Automatically generated on 2025-06-29
