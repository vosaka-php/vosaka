***

# DNSResolver

DNS Resolver



* Full name: `\venndev\vosaka\net\dns\DNSResolver`



## Properties


### nameservers



```php
private array $nameservers
```






***

### timeout



```php
private float $timeout
```






***

### cache



```php
private array $cache
```






***

### cacheSize



```php
private int $cacheSize
```






***

## Methods


### __construct



```php
public __construct(array $nameservers = [&#039;8.8.8.8:53&#039;, &#039;8.8.4.4:53&#039;], float $timeout = 5.0): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$nameservers` | **array** |  |
| `$timeout` | **float** |  |





***

### resolve

Resolve domain name

```php
public resolve(string $domain, \venndev\vosaka\net\dns\RecordType $type = RecordType::A): \venndev\vosaka\core\Result
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$domain` | **string** |  |
| `$type` | **\venndev\vosaka\net\dns\RecordType** |  |





***

### doResolve



```php
private doResolve(string $domain, \venndev\vosaka\net\dns\RecordType $type): \Generator
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$domain` | **string** |  |
| `$type` | **\venndev\vosaka\net\dns\RecordType** |  |





***

### queryNameserver

Query specific nameserver

```php
private queryNameserver(string $nameserver, string $packet, int $queryId): \Generator
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$nameserver` | **string** |  |
| `$packet` | **string** |  |
| `$queryId` | **int** |  |





***

### addToCache

Add to cache

```php
private addToCache(string $key, array $records, int $ttl): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$key` | **string** |  |
| `$records` | **array** |  |
| `$ttl` | **int** |  |





***

### clearCache

Clear cache

```php
public clearCache(): void
```












***

### resolveA

Resolve A records (IPv4)

```php
public resolveA(string $domain): \venndev\vosaka\core\Result
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$domain` | **string** |  |





***

### resolveAAAA

Resolve AAAA records (IPv6)

```php
public resolveAAAA(string $domain): \venndev\vosaka\core\Result
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$domain` | **string** |  |





***

### resolveMX

Resolve MX records

```php
public resolveMX(string $domain): \venndev\vosaka\core\Result
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$domain` | **string** |  |





***

### resolveTXT

Resolve TXT records

```php
public resolveTXT(string $domain): \venndev\vosaka\core\Result
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$domain` | **string** |  |





***

### resolveAll

Resolve all record types

```php
public resolveAll(string $domain): \venndev\vosaka\core\Result
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$domain` | **string** |  |





***

### doResolveAll



```php
private doResolveAll(string $domain): \Generator
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$domain` | **string** |  |





***


***
> Automatically generated on 2025-07-24
