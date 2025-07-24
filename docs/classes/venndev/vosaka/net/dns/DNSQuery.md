***

# DNSQuery

DNS Query builder



* Full name: `\venndev\vosaka\net\dns\DNSQuery`



## Properties


### domain



```php
private string $domain
```






***

### type



```php
private \venndev\vosaka\net\dns\RecordType $type
```






***

### class



```php
private \venndev\vosaka\net\dns\QueryClass $class
```






***

### id



```php
private int $id
```






***

### recursionDesired



```php
private bool $recursionDesired
```






***

## Methods


### __construct



```php
public __construct(string $domain): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$domain` | **string** |  |





***

### setType



```php
public setType(\venndev\vosaka\net\dns\RecordType $type): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$type` | **\venndev\vosaka\net\dns\RecordType** |  |





***

### setClass



```php
public setClass(\venndev\vosaka\net\dns\QueryClass $class): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$class` | **\venndev\vosaka\net\dns\QueryClass** |  |





***

### setRecursionDesired



```php
public setRecursionDesired(bool $desired): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$desired` | **bool** |  |





***

### build

Build DNS query packet

```php
public build(): string
```












***

### encodeDomain

Encode domain name

```php
private encodeDomain(string $domain): string
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$domain` | **string** |  |





***

### getId



```php
public getId(): int
```












***


***
> Automatically generated on 2025-07-24
