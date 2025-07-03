***

# UDPSock

UDPSock provides asynchronous UDP socket operations.

This class handles UDP socket creation, binding, sending, and receiving
with support for both IPv4 and IPv6 protocols. It integrates with the
VOsaka event loop for non-blocking operations.

* Full name: `\venndev\vosaka\net\udp\UDPSock`
* Parent class: [`\venndev\vosaka\net\SocketBase`](../SocketBase.md)
* This class is marked as **final** and can't be subclassed
* This class implements:
[`\venndev\vosaka\net\DatagramInterface`](../DatagramInterface.md)
* This class is a **Final class**



## Properties


### bound



```php
private bool $bound
```






***

### addr



```php
private string $addr
```






***

### port



```php
private int $port
```






***

### family



```php
private string $family
```






***

## Methods


### __construct



```php
private __construct(string $family = &quot;v4&quot;): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$family` | **string** |  |





***

### newV4

Create a new IPv4 UDP socket.

```php
public static newV4(): self
```



* This method is **static**.





**Return Value:**

New UDPSock instance for IPv4




***

### newV6

Create a new IPv6 UDP socket.

```php
public static newV6(): self
```



* This method is **static**.





**Return Value:**

New UDPSock instance for IPv6




***

### bind

Bind the socket to the specified address and port.

```php
public bind(string $addr): \venndev\vosaka\core\Result&lt;\venndev\vosaka\net\udp\UDPSock&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$addr` | **string** | Address in &#039;host:port&#039; format |


**Return Value:**

Result containing this UDPSock instance



**Throws:**
<p>If binding fails</p>

- [`InvalidArgumentException`](../../../../InvalidArgumentException.md)



***

### sendTo

Send data to a specific address.

```php
public sendTo(string $data, string $addr): \venndev\vosaka\core\Result&lt;int&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$data` | **string** | Data to send |
| `$addr` | **string** | Address in &#039;host:port&#039; format |


**Return Value:**

Number of bytes sent



**Throws:**
<p>If socket is not created or send fails</p>

- [`InvalidArgumentException`](../../../../InvalidArgumentException.md)



***

### receiveFrom

Receive data from any address.

```php
public receiveFrom(int $maxLength = 65535): \venndev\vosaka\core\Result&lt;array{data: string, peerAddr: string}&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$maxLength` | **int** | Maximum length of data to receive |


**Return Value:**

Received data and peer address



**Throws:**
<p>If socket is not bound or receive fails</p>

- [`InvalidArgumentException`](../../../../InvalidArgumentException.md)



***

### setReuseAddr

Set SO_REUSEADDR socket option.

```php
public setReuseAddr(bool $reuseAddr): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$reuseAddr` | **bool** | Whether to enable address reuse |


**Return Value:**

This instance for method chaining




***

### setReusePort

Set SO_REUSEPORT socket option.

```php
public setReusePort(bool $reusePort): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$reusePort` | **bool** | Whether to enable port reuse |


**Return Value:**

This instance for method chaining




***

### setBroadcast

Set SO_BROADCAST socket option.

```php
public setBroadcast(bool $broadcast): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$broadcast` | **bool** | Whether to enable broadcast |


**Return Value:**

This instance for method chaining




***

### getLocalAddr

Get the local address of the bound socket.

```php
public getLocalAddr(): string
```









**Return Value:**

Local address or empty string if not bound




***

### isClosed

Check if the socket is closed.

```php
public isClosed(): bool
```









**Return Value:**

True if socket is closed




***

### close

Close the socket and cleanup resources.

```php
public close(): void
```












***


## Inherited methods


### createContext



```php
protected static createContext(array $options = []): resource
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$options` | **array** |  |





***

### applySocketOptions



```php
protected static applySocketOptions(mixed $socket, array $options): void
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$socket` | **mixed** |  |
| `$options` | **array** |  |





***

### validatePath



```php
protected static validatePath(string $path): void
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** |  |





***

### parseAddr



```php
protected static parseAddr(string $addr): array
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$addr` | **string** |  |





***

### addToEventLoop



```php
protected static addToEventLoop(mixed $socket): void
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$socket` | **mixed** |  |





***

### removeFromEventLoop



```php
protected static removeFromEventLoop(mixed $socket): void
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$socket` | **mixed** |  |





***


***
> Automatically generated on 2025-07-03
