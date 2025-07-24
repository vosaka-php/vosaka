***

# UDP

UDP factory



* Full name: `\venndev\vosaka\net\udp\UDP`




## Methods


### bind

Create UDP socket bound to address

```php
public static bind(string|\venndev\vosaka\net\contracts\AddressInterface $address, array $options = []): \venndev\vosaka\core\Result&lt;\venndev\vosaka\net\udp\UDPSocket&gt;
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$address` | **string&#124;\venndev\vosaka\net\contracts\AddressInterface** |  |
| `$options` | **array** |  |





***

### doBind

Bind UDP socket to a specific address

```php
private static doBind(string|\venndev\vosaka\net\contracts\AddressInterface $address, array $options): \Generator&lt;\venndev\vosaka\net\udp\UDPSocket&gt;
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$address` | **string&#124;\venndev\vosaka\net\contracts\AddressInterface** |  |
| `$options` | **array** |  |




**Throws:**

- [`NetworkException`](../exceptions/NetworkException.md)



***

### socket

Create unbound UDP socket

```php
public static socket(string $family = &#039;v4&#039;, array $options = []): \venndev\vosaka\core\Result&lt;\venndev\vosaka\net\udp\UDPSocket&gt;
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$family` | **string** | &#039;v4&#039; or &#039;v6&#039; |
| `$options` | **array** | Socket options |





***

### doSocket

Create an unbound UDP socket

```php
private static doSocket(string $family, array $options): \Generator&lt;\venndev\vosaka\net\udp\UDPSocket&gt;
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$family` | **string** | &#039;v4&#039; or &#039;v6&#039; |
| `$options` | **array** | Socket options |




**Throws:**

- [`NetworkException`](../exceptions/NetworkException.md)



***


***
> Automatically generated on 2025-07-24
