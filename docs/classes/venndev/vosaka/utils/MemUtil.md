***

# MemUtil

MemUtil class for memory-related utility functions and conversions.

This utility class provides various methods for working with memory measurements
and conversions. It includes functions for converting between different memory
units and for retrieving current memory usage statistics from PHP's memory
management system.

All memory conversion functions validate input to ensure non-negative values
and throw appropriate exceptions for invalid input. Memory usage functions
return current and peak memory usage in different units for monitoring
and debugging purposes.

* Full name: `\venndev\vosaka\utils\MemUtil`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**




## Methods


### toB

Convert a value to bytes by multiplying by 1024.

```php
public static toB(int $value): int
```

Converts the input value to bytes assuming the input represents
kilobytes. This is a simple multiplication by 1024.

* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$value` | **int** | The value in kilobytes to convert to bytes |


**Return Value:**

The value converted to bytes



**Throws:**
<p>If the value is negative</p>

- [`InvalidArgumentException`](../../../InvalidArgumentException.md)



***

### toKB

Convert a value from megabytes to bytes.

```php
public static toKB(int $value): int
```

Converts the input value from megabytes to bytes by multiplying
by 1024 * 1024 (1,048,576). This is useful for converting memory
limits specified in MB to the byte values used internally.

* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$value` | **int** | The value in megabytes to convert to bytes |


**Return Value:**

The value converted to bytes



**Throws:**
<p>If the value is negative</p>

- [`InvalidArgumentException`](../../../InvalidArgumentException.md)



***

### getKBUsed

Get the current memory usage in kilobytes.

```php
public static getKBUsed(): int
```

Returns the current memory usage of the PHP script in kilobytes.
Uses memory_get_usage(true) to get the real memory usage including
memory allocated by the system but not used by emalloc().

* This method is **static**.





**Return Value:**

Current memory usage in kilobytes




***

### getMBUsed

Get the current memory usage in megabytes.

```php
public static getMBUsed(): int
```

Returns the current memory usage of the PHP script in megabytes.
Uses memory_get_usage(true) to get the real memory usage including
memory allocated by the system but not used by emalloc().

* This method is **static**.





**Return Value:**

Current memory usage in megabytes




***

### getKBPeak

Get the peak memory usage in kilobytes.

```php
public static getKBPeak(): int
```

Returns the peak memory usage of the PHP script in kilobytes since
the script started. Uses memory_get_peak_usage(true) to get the real
peak memory usage including memory allocated by the system.

* This method is **static**.





**Return Value:**

Peak memory usage in kilobytes




***

### getMBPeak

Get the peak memory usage in megabytes.

```php
public static getMBPeak(): int
```

Returns the peak memory usage of the PHP script in megabytes since
the script started. Uses memory_get_peak_usage(true) to get the real
peak memory usage including memory allocated by the system.

* This method is **static**.





**Return Value:**

Peak memory usage in megabytes




***


***
> Automatically generated on 2025-07-08
