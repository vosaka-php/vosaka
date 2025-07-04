***

# DNSNetworkException

DNS Network Exception

Thrown when network-related errors occur during DNS operations,
such as socket creation failures or connection issues.

* Full name: `\venndev\vosaka\net\dns\exceptions\DNSNetworkException`
* Parent class: [`\venndev\vosaka\net\dns\exceptions\DNSQueryException`](./DNSQueryException.md)



## Properties


### networkErrorCode

Network error code

```php
private int|null $networkErrorCode
```






***

## Methods


### __construct

Create DNS network exception

```php
public __construct(string $message, int|null $networkErrorCode = null, array{hostname: string, type: string, server?: string}|null $query = null, int $code, \Throwable|null $previous = null): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$message` | **string** | Exception message |
| `$networkErrorCode` | **int&#124;null** | Network-specific error code |
| `$query` | **array{hostname: string, type: string, server?: string}&#124;null** | DNS query that failed |
| `$code` | **int** | Exception code |
| `$previous` | **\Throwable&#124;null** | Previous exception |





***

### getNetworkErrorCode

Get the network error code

```php
public getNetworkErrorCode(): int|null
```












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
> Automatically generated on 2025-07-04
