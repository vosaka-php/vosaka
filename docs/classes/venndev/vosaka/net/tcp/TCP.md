***

# TCP

TCP client/server factory



* Full name: `\venndev\vosaka\net\tcp\TCP`




## Methods


### connect

Connect to TCP server

```php
public static connect(string|\venndev\vosaka\net\contracts\AddressInterface $address, array $options = []): \venndev\vosaka\core\Result&lt;\venndev\vosaka\net\tcp\TCPConnection&gt;
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$address` | **string&#124;\venndev\vosaka\net\contracts\AddressInterface** |  |
| `$options` | **array** | Connection options |





***

### doConnect

Internal method to handle TCP connection

```php
private static doConnect(string|\venndev\vosaka\net\contracts\AddressInterface $address, array $options): \Generator&lt;\venndev\vosaka\net\tcp\TCPConnection&gt;
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$address` | **string&#124;\venndev\vosaka\net\contracts\AddressInterface** |  |
| `$options` | **array** | Connection options |




**Throws:**

- [`ConnectionException`](../exceptions/ConnectionException.md)



***

### listen

Create TCP server

```php
public static listen(string|\venndev\vosaka\net\contracts\AddressInterface $address, array $options = []): \venndev\vosaka\core\Result&lt;\venndev\vosaka\net\tcp\TCPServer&gt;
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$address` | **string&#124;\venndev\vosaka\net\contracts\AddressInterface** |  |
| `$options` | **array** | Server options |





***

### doListen

Internal method to handle TCP server creation

```php
private static doListen(string|\venndev\vosaka\net\contracts\AddressInterface $address, array $options): \Generator&lt;\venndev\vosaka\net\tcp\TCPServer&gt;
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$address` | **string&#124;\venndev\vosaka\net\contracts\AddressInterface** |  |
| `$options` | **array** | Server options |




**Throws:**

- [`BindException`](../exceptions/BindException.md)



***


***
> Automatically generated on 2025-07-24
