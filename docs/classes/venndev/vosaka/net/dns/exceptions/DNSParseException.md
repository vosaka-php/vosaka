***

# DNSParseException

DNS Parse Exception

Thrown when DNS response parsing fails due to malformed
or invalid response data.

* Full name: `\venndev\vosaka\net\dns\exceptions\DNSParseException`
* Parent class: [`\venndev\vosaka\net\dns\exceptions\DNSException`](./DNSException.md)



## Properties


### parseOffset

Offset in response where parsing failed

```php
private int|null $parseOffset
```






***

## Methods


### __construct

Create DNS parse exception

```php
public __construct(string $message, string|null $responseData = null, int|null $parseOffset = null, int $code, \Throwable|null $previous = null): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$message` | **string** | Exception message |
| `$responseData` | **string&#124;null** | DNS response that failed to parse |
| `$parseOffset` | **int&#124;null** | Offset where parsing failed |
| `$code` | **int** | Exception code |
| `$previous` | **\Throwable&#124;null** | Previous exception |





***

### getParseOffset

Get the offset where parsing failed

```php
public getParseOffset(): int|null
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
> Automatically generated on 2025-07-01
