***

# AddressRecord





* Full name: `\venndev\vosaka\net\dns\model\AddressRecord`
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

### address



```php
public string $address
```






***

## Methods


### __construct



```php
public __construct(bool $success, ?string $error, string $address): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$success` | **bool** |  |
| `$error` | **?string** |  |
| `$address` | **string** |  |





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

### getAddress

Get the IP address

```php
public getAddress(): string
```












***

### isIPv4

Check if this is an IPv4 address

```php
public isIPv4(): bool
```












***

### isIPv6

Check if this is an IPv6 address

```php
public isIPv6(): bool
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


***
> Automatically generated on 2025-07-04
