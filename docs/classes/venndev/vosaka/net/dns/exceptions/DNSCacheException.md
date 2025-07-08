***

# DNSCacheException

DNS Cache Exception

Thrown when DNS cache operations fail or encounter errors.

* Full name: `\venndev\vosaka\net\dns\exceptions\DNSCacheException`
* Parent class: [`\venndev\vosaka\net\dns\exceptions\DNSException`](./DNSException.md)



## Properties


### cacheOperation

Cache operation that failed

```php
private string $cacheOperation
```






***

### cacheKey

Cache key involved in the failure

```php
private string|null $cacheKey
```






***

## Methods


### __construct

Create DNS cache exception

```php
public __construct(string $message, string $cacheOperation, string|null $cacheKey = null, int $code, \Throwable|null $previous = null): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$message` | **string** | Exception message |
| `$cacheOperation` | **string** | Cache operation that failed (get, set, delete, etc.) |
| `$cacheKey` | **string&#124;null** | Cache key involved |
| `$code` | **int** | Exception code |
| `$previous` | **\Throwable&#124;null** | Previous exception |





***

### getCacheOperation

Get the cache operation that failed

```php
public getCacheOperation(): string
```












***

### getCacheKey

Get the cache key involved in the failure

```php
public getCacheKey(): string|null
```












***


## Inherited methods


### __construct

Create DNS exception

```php
public __construct(string $message = &quot;&quot;, int $code, \Throwable|null $previous = null, array{hostname: string, type: string, server?: string}|null $query = null, string|null $responseData = null, int|null $dnsErrorCode = null): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$message` | **string** | Exception message |
| `$code` | **int** | Exception code |
| `$previous` | **\Throwable&#124;null** | Previous exception |
| `$query` | **array{hostname: string, type: string, server?: string}&#124;null** | DNS query that caused the exception |
| `$responseData` | **string&#124;null** | DNS response data (if available) |
| `$dnsErrorCode` | **int&#124;null** | DNS-specific error code |





***

### getQuery

Get the DNS query that caused this exception

```php
public getQuery(): array{hostname: string, type: string, server?: string}|null
```












***

### getResponseData

Get the DNS response data (if available)

```php
public getResponseData(): string|null
```












***

### getDNSErrorCode

Get the DNS-specific error code

```php
public getDNSErrorCode(): int|null
```












***

### getDNSErrorDescription

Get human-readable DNS error code description

```php
public getDNSErrorDescription(): string|null
```












***

### toArray

Get detailed exception information as array

```php
public toArray(): array&lt;string,mixed&gt;
```












***


***
> Automatically generated on 2025-07-08
