***

# DNSTimeoutException

DNS Timeout Exception

Thrown when a DNS query times out before receiving a response.

* Full name: `\venndev\vosaka\net\dns\exceptions\DNSTimeoutException`
* Parent class: [`\venndev\vosaka\net\dns\exceptions\DNSQueryException`](./DNSQueryException.md)



## Properties


### timeoutDuration

Timeout duration in seconds

```php
private int $timeoutDuration
```






***

## Methods


### __construct

Create DNS timeout exception

```php
public __construct(string $message, int $timeoutDuration, array{hostname: string, type: string, server?: string}|null $query = null, int $code, \Throwable|null $previous = null): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$message` | **string** | Exception message |
| `$timeoutDuration` | **int** | Timeout duration in seconds |
| `$query` | **array{hostname: string, type: string, server?: string}&#124;null** | DNS query that timed out |
| `$code` | **int** | Exception code |
| `$previous` | **\Throwable&#124;null** | Previous exception |





***

### getTimeoutDuration

Get the timeout duration

```php
public getTimeoutDuration(): int
```









**Return Value:**

Timeout duration in seconds




***


## Inherited methods


### __construct

Create DNS query exception

```php
public __construct(string $message, array{hostname: string, type: string, server?: string}|null $query = null, int $code, \Throwable|null $previous = null): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$message` | **string** | Exception message |
| `$query` | **array{hostname: string, type: string, server?: string}&#124;null** | DNS query that failed |
| `$code` | **int** | Exception code |
| `$previous` | **\Throwable&#124;null** | Previous exception |





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
> Automatically generated on 2025-07-02
