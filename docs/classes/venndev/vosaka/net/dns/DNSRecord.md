***

# DNSRecord

DNS Record representation



* Full name: `\venndev\vosaka\net\dns\DNSRecord`



## Properties


### name



```php
public string $name
```






***

### type



```php
public \venndev\vosaka\net\dns\RecordType $type
```






***

### class



```php
public \venndev\vosaka\net\dns\QueryClass $class
```






***

### ttl



```php
public int $ttl
```






***

### data



```php
public mixed $data
```






***

## Methods


### __construct



```php
public __construct(string $name, \venndev\vosaka\net\dns\RecordType $type, \venndev\vosaka\net\dns\QueryClass $class, int $ttl, mixed $data): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$name` | **string** |  |
| `$type` | **\venndev\vosaka\net\dns\RecordType** |  |
| `$class` | **\venndev\vosaka\net\dns\QueryClass** |  |
| `$ttl` | **int** |  |
| `$data` | **mixed** |  |





***

### __toString



```php
public __toString(): string
```












***

### dataToString



```php
private dataToString(): string
```












***


***
> Automatically generated on 2025-07-24
