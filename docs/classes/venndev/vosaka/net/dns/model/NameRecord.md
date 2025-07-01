***

# NameRecord





* Full name: `\venndev\vosaka\net\dns\model\NameRecord`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### success



```php
public bool $success
```






***

### error



```php
public ?string $error
```






***

### name



```php
public string $name
```






***

## Methods


### __construct



```php
public __construct(bool $success, ?string $error, string $name): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$success` | **bool** |  |
| `$error` | **?string** |  |
| `$name` | **string** |  |





***

### isSuccess

Check if the record parsing was successful

```php
public isSuccess(): bool
```












***

### getError

Get error message if parsing failed

```php
public getError(): ?string
```












***

### getName

Get the name value

```php
public getName(): string
```












***

### isFQDN

Check if the name is a fully qualified domain name (FQDN)

```php
public isFQDN(): bool
```












***

### getNameWithoutDot

Get the name without trailing dot if present

```php
public getNameWithoutDot(): string
```












***

### getFQDN

Get the name with trailing dot (FQDN format)

```php
public getFQDN(): string
```












***

### getDomainParts

Get the domain parts as array

```php
public getDomainParts(): array
```












***

### getTLD

Get the top-level domain

```php
public getTLD(): string
```












***

### getSubdomains

Get the subdomain parts (everything except TLD)

```php
public getSubdomains(): array
```












***

### isSubdomainOf

Check if this is a subdomain of another domain

```php
public isSubdomainOf(string $domain): bool
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$domain` | **string** |  |





***

### getDomainLevels

Get the number of domain levels

```php
public getDomainLevels(): int
```












***

### isRoot

Check if the name represents a root domain

```php
public isRoot(): bool
```












***

### toArray

Convert to array format for backwards compatibility

```php
public toArray(): array
```












***

### fromArray

Create from array data

```php
public static fromArray(array $data): self
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$data` | **array** |  |





***

### __toString

String representation

```php
public __toString(): string
```












***

### equals

Compare names (case-insensitive)

```php
public equals(string $otherName): bool
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$otherName` | **string** |  |





***

### matches

Check if name matches a pattern (supports wildcards)

```php
public matches(string $pattern): bool
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$pattern` | **string** |  |





***


***
> Automatically generated on 2025-07-01
