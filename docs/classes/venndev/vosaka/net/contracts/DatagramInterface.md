***

# DatagramInterface

Interface for datagram (UDP) sockets



* Full name: `\venndev\vosaka\net\contracts\DatagramInterface`



## Methods


### sendTo

Send data to address

```php
public sendTo(string $data, \venndev\vosaka\net\contracts\AddressInterface $address): \venndev\vosaka\core\Result&lt;int&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$data` | **string** |  |
| `$address` | **\venndev\vosaka\net\contracts\AddressInterface** |  |


**Return Value:**

Bytes sent




***

### receiveFrom

Receive data from any address

```php
public receiveFrom(int $maxLength = 65535): \venndev\vosaka\core\Result&lt;array{data: string, address: \venndev\vosaka\net\contracts\AddressInterface}&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$maxLength` | **int** |  |





***

### getLocalAddress

Get local address

```php
public getLocalAddress(): \venndev\vosaka\net\contracts\AddressInterface
```












***

### close

Close socket

```php
public close(): void
```












***

### isClosed

Check if closed

```php
public isClosed(): bool
```












***


***
> Automatically generated on 2025-07-24
