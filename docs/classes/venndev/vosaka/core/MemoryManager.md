***

# MemoryManager





* Full name: `\venndev\vosaka\core\MemoryManager`


## Constants

| Constant | Visibility | Type | Value |
|:---------|:-----------|:-----|:------|
|`NORMAL_GC_THRESHOLD`|private| |0.6|
|`AGGRESSIVE_GC_THRESHOLD`|private| |0.75|
|`CRITICAL_GC_THRESHOLD`|private| |0.85|
|`EMERGENCY_THRESHOLD`|private| |0.95|
|`NORMAL_CHECK_INTERVAL`|private| |100|
|`AGGRESSIVE_CHECK_INTERVAL`|private| |25|
|`CRITICAL_CHECK_INTERVAL`|private| |10|

## Properties


### memoryLimit



```php
private float $memoryLimit
```






***

### gcInterval



```php
private int $gcInterval
```






***

### taskCounter



```php
private int $taskCounter
```






***

### lastMemoryUsage



```php
private float $lastMemoryUsage
```






***

### memoryCheckCounter



```php
private int $memoryCheckCounter
```






***

### baselineMemory



```php
private float $baselineMemory
```






***

### aggressiveGcCounter



```php
private int $aggressiveGcCounter
```






***

## Methods


### __construct



```php
public __construct(float $memoryLimit = 64, int $gcInterval = 50): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$memoryLimit` | **float** |  |
| `$gcInterval` | **int** |  |





***

### init



```php
public init(): void
```












***

### checkMemoryUsage



```php
public checkMemoryUsage(): bool
```












***

### collectGarbage



```php
public collectGarbage(): void
```












***

### forceGarbageCollection



```php
public forceGarbageCollection(): void
```












***

### getCheckInterval



```php
private getCheckInterval(float $memoryPercentage): int
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$memoryPercentage` | **float** |  |





***

### performGarbageCollection



```php
private performGarbageCollection(): void
```












***

### aggressiveCleanup



```php
private aggressiveCleanup(): void
```












***

### criticalCleanup



```php
private criticalCleanup(): void
```












***

### emergencyCleanup



```php
private emergencyCleanup(): void
```












***

### getMemoryLimit



```php
public getMemoryLimit(): float
```












***

### getMemoryPercentage



```php
public getMemoryPercentage(): float
```












***

### getBaselineMemory



```php
public getBaselineMemory(): float
```












***

### getMemoryGrowth



```php
public getMemoryGrowth(): float
```












***

### isMemoryStable



```php
public isMemoryStable(): bool
```












***

### getDetailedStats



```php
public getDetailedStats(): array
```












***


***
> Automatically generated on 2025-06-26
