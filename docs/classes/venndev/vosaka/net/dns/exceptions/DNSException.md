***

# DNSException

Base DNS Exception

This is the base exception class for all DNS-related errors.
It provides common functionality and serves as the parent class
for all specific DNS exception types.

* Full name: `\venndev\vosaka\net\dns\exceptions\DNSException`
* Parent class: [`Exception`](../../../../../Exception.md)



## Properties


### query

DNS query that caused the exception

```php
protected array{hostname: string, type: string, server?: string}|null $query
```






***

### responseData

DNS response data (if available)

```php
protected string|null $responseData
```






***

### dnsErrorCode

DNS error code (if applicable)

```php
protected int|null $dnsErrorCode
```






***

## Methods


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
