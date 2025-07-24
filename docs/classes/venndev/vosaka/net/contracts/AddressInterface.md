***

# AddressInterface

Base interface for network addresses



* Full name: `\venndev\vosaka\net\contracts\AddressInterface`



## Methods


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

### parse

Parse address from string

```php
public static parse(string $address): static
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$address` | **string** |  |





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


***
> Automatically generated on 2025-07-24
