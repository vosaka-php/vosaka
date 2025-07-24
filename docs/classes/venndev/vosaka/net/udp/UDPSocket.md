***

# UDPSocket

UDP Socket implementation



* Full name: `\venndev\vosaka\net\udp\UDPSocket`
* This class implements:
[`\venndev\vosaka\net\contracts\DatagramInterface`](../contracts/DatagramInterface.md)



## Properties


### socket



```php
private $socket
```






***

### closed



```php
private bool $closed
```






***

### localAddress



```php
private ?\venndev\vosaka\net\contracts\AddressInterface $localAddress
```






***

### eventLoop



```php
private \venndev\vosaka\net\EventLoopIntegration $eventLoop
```






***

### receiveQueue



```php
private array $receiveQueue
```






***

### readRegistered



```php
private bool $readRegistered
```






***

## Methods


### __construct



```php
public __construct(mixed $socket): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$socket` | **mixed** |  |





***

### handleRead

Handle readable event

```php
public handleRead(mixed $socket): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$socket` | **mixed** |  |





***

### sendTo

Send data to a specific address

```php
public sendTo(string $data, \venndev\vosaka\net\contracts\AddressInterface $address): \venndev\vosaka\core\Result&lt;int&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$data` | **string** | The data to send |
| `$address` | **\venndev\vosaka\net\contracts\AddressInterface** | The destination address |





***

### doSendTo

Send data to a specific address

```php
private doSendTo(string $data, \venndev\vosaka\net\contracts\AddressInterface $address): \Generator&lt;int&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$data` | **string** | The data to send |
| `$address` | **\venndev\vosaka\net\contracts\AddressInterface** | The destination address |




**Throws:**

- [`NetworkException`](../exceptions/NetworkException.md)



***

### receiveFrom

Receive data from any address

```php
public receiveFrom(int $maxLength = 65535): \venndev\vosaka\core\Result&lt;array{data: string, address: \venndev\vosaka\net\contracts\AddressInterface|null}&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$maxLength` | **int** | Maximum length of data to receive |





***

### doReceiveFrom

Receive data from any address

```php
private doReceiveFrom(int $maxLength): \Generator&lt;array{data: string, address: \venndev\vosaka\net\contracts\AddressInterface|null}&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$maxLength` | **int** | Maximum length of data to receive |




**Throws:**

- [`NetworkException`](../exceptions/NetworkException.md)



***

### getLocalAddress

Get the local address of the socket

```php
public getLocalAddress(): \venndev\vosaka\net\contracts\AddressInterface
```












***

### close

Close the socket and clean up resources

```php
public close(): void
```












***

### isClosed

Check if the socket is closed

```php
public isClosed(): bool
```












***

### setOption

Set socket option

```php
public setOption(int $level, int $option, mixed $value): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$level` | **int** |  |
| `$option` | **int** |  |
| `$value` | **mixed** |  |





***

### setBroadcast

Enable broadcast

```php
public setBroadcast(bool $enable): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$enable` | **bool** |  |





***

### setMulticastTTL

Set multicast TTL

```php
public setMulticastTTL(int $ttl): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$ttl` | **int** |  |





***

### joinMulticastGroup

Join multicast group

```php
public joinMulticastGroup(string $group, string $interface = &#039;0.0.0.0&#039;): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$group` | **string** |  |
| `$interface` | **string** |  |





***

### leaveMulticastGroup

Leave multicast group

```php
public leaveMulticastGroup(string $group, string $interface = &#039;0.0.0.0&#039;): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$group` | **string** |  |
| `$interface` | **string** |  |





***


***
> Automatically generated on 2025-07-24
