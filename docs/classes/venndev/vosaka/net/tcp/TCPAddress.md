***

# TCPAddress





* Full name: `\venndev\vosaka\net\tcp\TCPAddress`
* This class implements:
[`\venndev\vosaka\net\contracts\AddressInterface`](../contracts/AddressInterface.md)



## Properties


### host



```php
private string $host
```






***

### port



```php
private int $port
```






***

### family



```php
private int $family
```






***

## Methods


### __construct



```php
public __construct(string $host, int $port): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$host` | **string** |  |
| `$port` | **int** |  |





***

### toString

Convert address to string representation

```php
public toString(): string
```












***

### getFamily

Get the address family

```php
public getFamily(): int
```












***

### isLoopback

Check if the address is a loopback address

```php
public isLoopback(): bool
```












***

### getHost

Get the host and port

```php
public getHost(): string
```












***

### getPort

Get the port number

```php
public getPort(): int
```












***

### parse

Parse a string address into a TCPAddress object

```php
public static parse(string $address): static
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$address` | **string** | Address in the format host:port or [host]:port for IPv6 |




**Throws:**

- [`NetworkException`](../exceptions/NetworkException.md)



***


***
> Automatically generated on 2025-07-24
