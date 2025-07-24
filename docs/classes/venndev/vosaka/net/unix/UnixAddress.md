***

# UnixAddress

Unix Socket Address implementation



* Full name: `\venndev\vosaka\net\unix\UnixAddress`
* This class implements:
[`\venndev\vosaka\net\contracts\AddressInterface`](../contracts/AddressInterface.md)



## Properties


### path



```php
private string $path
```






***

### abstract



```php
private bool $abstract
```






***

## Methods


### __construct



```php
public __construct(string $path, bool $abstract = false): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** |  |
| `$abstract` | **bool** |  |





***

### getHost

Get the host part of the address

```php
public getHost(): string
```












***

### getPort

Get the port part of the address

```php
public getPort(): int
```












***

### toString

Get string representation of the address

```php
public toString(): string
```












***

### getFamily

Get the address family (AF_INET, AF_INET6, AF_UNIX)

```php
public getFamily(): int
```












***

### isLoopback

Check if this is a loopback address

```php
public isLoopback(): bool
```












***

### getPath



```php
public getPath(): string
```












***

### isAbstract



```php
public isAbstract(): bool
```












***

### parse

Create a UnixAddress from a string representation
If the address starts with a null byte, it's an abstract socket

```php
public static parse(string $address): static
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$address` | **string** |  |





***

### validate

Validate Unix socket path

```php
public static validate(string $path): void
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** |  |





***


***
> Automatically generated on 2025-07-24
