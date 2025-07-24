***

# DNSResponse

DNS Response parser



* Full name: `\venndev\vosaka\net\dns\DNSResponse`



## Properties


### data



```php
private string $data
```






***

### offset



```php
private int $offset
```






***

### id



```php
private int $id
```






***

### flags



```php
private int $flags
```






***

### responseCode



```php
private \venndev\vosaka\net\dns\ResponseCode $responseCode
```






***

### questions



```php
private array $questions
```






***

### answers



```php
private array $answers
```






***

### authority



```php
private array $authority
```






***

### additional



```php
private array $additional
```






***

## Methods


### __construct



```php
public __construct(string $data): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$data` | **string** |  |





***

### parse

Parse DNS response

```php
private parse(): void
```












***

### parseQuestion

Parse question section

```php
private parseQuestion(): array
```












***

### parseRecord

Parse resource record

```php
private parseRecord(): \venndev\vosaka\net\dns\DNSRecord
```












***

### parseDomain

Parse domain name with compression

```php
private parseDomain(): string
```












***

### parseRData

Parse record data based on type

```php
private parseRData(\venndev\vosaka\net\dns\RecordType $type, int $length): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$type` | **\venndev\vosaka\net\dns\RecordType** |  |
| `$length` | **int** |  |





***

### parseDomainInRData

Parse domain in RDATA

```php
private parseDomainInRData(string $data): string
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$data` | **string** |  |





***

### parseMX

Parse MX record

```php
private parseMX(string $data): array
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$data` | **string** |  |





***

### parseTXT

Parse TXT record

```php
private parseTXT(string $data): array
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$data` | **string** |  |





***

### parseSRV

Parse SRV record

```php
private parseSRV(string $data): array
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$data` | **string** |  |





***

### parseSOA

Parse SOA record

```php
private parseSOA(string $data): array
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$data` | **string** |  |





***

### getId



```php
public getId(): int
```












***

### getResponseCode



```php
public getResponseCode(): \venndev\vosaka\net\dns\ResponseCode
```












***

### getAnswers



```php
public getAnswers(): array
```












***

### getAuthority



```php
public getAuthority(): array
```












***

### getAdditional



```php
public getAdditional(): array
```












***

### isAuthoritative



```php
public isAuthoritative(): bool
```












***

### isTruncated



```php
public isTruncated(): bool
```












***


***
> Automatically generated on 2025-07-24
