***

# DNSConfigurationException

DNS Configuration Exception

Thrown when DNS client configuration is invalid or contains errors.

* Full name: `\venndev\vosaka\net\dns\exceptions\DNSConfigurationException`
* Parent class: [`\venndev\vosaka\net\dns\exceptions\DNSException`](./DNSException.md)



## Properties


### configParameter

Configuration parameter that caused the error

```php
private string|null $configParameter
```






***

### configValue

Invalid configuration value

```php
private mixed $configValue
```






***

## Methods


### __construct

Create DNS configuration exception

```php
public __construct(string $message, string|null $configParameter = null, mixed $configValue = null, int $code, \Throwable|null $previous = null): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$message` | **string** | Exception message |
| `$configParameter` | **string&#124;null** | Configuration parameter name |
| `$configValue` | **mixed** | Invalid configuration value |
| `$code` | **int** | Exception code |
| `$previous` | **\Throwable&#124;null** | Previous exception |





***

### getConfigParameter

Get the configuration parameter that caused the error

```php
public getConfigParameter(): string|null
```












***

### getConfigValue

Get the invalid configuration value

```php
public getConfigValue(): mixed
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
> Automatically generated on 2025-07-14
