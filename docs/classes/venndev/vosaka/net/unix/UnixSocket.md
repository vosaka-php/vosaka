***

# UnixSocket

Unix socket factory



* Full name: `\venndev\vosaka\net\unix\UnixSocket`




## Methods


### connect

Connect to Unix socket

```php
public static connect(string|\venndev\vosaka\net\unix\UnixAddress $address, array $options = []): \venndev\vosaka\core\Result&lt;\venndev\vosaka\net\unix\UnixConnection&gt;
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$address` | **string&#124;\venndev\vosaka\net\unix\UnixAddress** |  |
| `$options` | **array** |  |





***

### doConnect

Connect to a Unix socket

```php
private static doConnect(string|\venndev\vosaka\net\unix\UnixAddress $address, array $options): \Generator&lt;\venndev\vosaka\net\unix\UnixConnection&gt;
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$address` | **string&#124;\venndev\vosaka\net\unix\UnixAddress** |  |
| `$options` | **array** |  |




**Throws:**

- [`ConnectionException`](../exceptions/ConnectionException.md)



***

### listen

Create Unix socket server

```php
public static listen(string|\venndev\vosaka\net\unix\UnixAddress $address, array $options = []): \venndev\vosaka\core\Result&lt;\venndev\vosaka\net\unix\UnixServer&gt;
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$address` | **string&#124;\venndev\vosaka\net\unix\UnixAddress** |  |
| `$options` | **array** |  |





***

### doListen

Create a Unix socket server

```php
private static doListen(string|\venndev\vosaka\net\unix\UnixAddress $address, array $options): \Generator&lt;\venndev\vosaka\net\unix\UnixServer&gt;
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$address` | **string&#124;\venndev\vosaka\net\unix\UnixAddress** |  |
| `$options` | **array** |  |




**Throws:**

- [`BindException`](../exceptions/BindException.md)



***

### datagram

Create Unix datagram socket (SOCK_DGRAM)

```php
public static datagram(string|\venndev\vosaka\net\unix\UnixAddress $address, array $options = []): \venndev\vosaka\core\Result&lt;\venndev\vosaka\net\udp\UDPSocket&gt;
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$address` | **string&#124;\venndev\vosaka\net\unix\UnixAddress** |  |
| `$options` | **array** |  |





***

### doDatagram

Create a Unix datagram socket

```php
private static doDatagram(string|\venndev\vosaka\net\unix\UnixAddress $address, array $options): \Generator&lt;\venndev\vosaka\net\udp\UDPSocket&gt;
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$address` | **string&#124;\venndev\vosaka\net\unix\UnixAddress** |  |
| `$options` | **array** |  |




**Throws:**

- [`BindException`](../exceptions/BindException.md)



***


***
> Automatically generated on 2025-07-24
