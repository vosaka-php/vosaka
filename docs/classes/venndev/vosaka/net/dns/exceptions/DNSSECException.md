***

# DNSSECException

DNSSEC Exception

Thrown when DNSSEC validation fails or encounters errors.

* Full name: `\venndev\vosaka\net\dns\exceptions\DNSSECException`
* Parent class: [`\venndev\vosaka\net\dns\exceptions\DNSException`](./DNSException.md)



## Properties


### validationError

DNSSEC validation error type

```php
private string $validationError
```






***

### dnssecRecords

DNSSEC records involved in the failure

```php
private array $dnssecRecords
```






***

## Methods


### __construct

Create DNSSEC exception

```php
public __construct(string $message, string $validationError, array $dnssecRecords = [], array{hostname: string, type: string, server?: string}|null $query = null, int $code, \Throwable|null $previous = null): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$message` | **string** | Exception message |
| `$validationError` | **string** | Type of DNSSEC validation error |
| `$dnssecRecords` | **array** | DNSSEC records involved |
| `$query` | **array{hostname: string, type: string, server?: string}&#124;null** | DNS query that failed validation |
| `$code` | **int** | Exception code |
| `$previous` | **\Throwable&#124;null** | Previous exception |





***

### getValidationError

Get the DNSSEC validation error type

```php
public getValidationError(): string
```












***

### getDNSsecRecords

Get the DNSSEC records involved in the failure

```php
public getDNSsecRecords(): array
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
> Automatically generated on 2025-07-02
