***

# UDPSock





* Full name: `\venndev\vosaka\net\tcp\UDPSock`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### socket



```php
private mixed $socket
```






***

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

### options



```php
private array $options
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



```php
public static newV4(): self
```



* This method is **static**.








***

### newV6



```php
public static newV6(): self
```



* This method is **static**.








***

### bind

Bind the socket to the specified address and port

```php
public bind(string $addr): \venndev\vosaka\core\Result&lt;\venndev\vosaka\net\tcp\UDPSock&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$addr` | **string** | Address in &#039;host:port&#039; format |





***

### sendTo

Send data to a specific address

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




***

### receiveFrom

Receive data from any address

```php
public receiveFrom(int $maxLength = 65535): \venndev\vosaka\core\Result&lt;array{data: string, peerAddr: string}&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$maxLength` | **int** | Maximum length of data to receive |





***

### setReuseAddr



```php
public setReuseAddr(bool $reuseAddr): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$reuseAddr` | **bool** |  |





***

### setReusePort



```php
public setReusePort(bool $reusePort): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$reusePort` | **bool** |  |





***

### setBroadcast



```php
public setBroadcast(bool $broadcast): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$broadcast` | **bool** |  |





***

### parseAddr



```php
private parseAddr(string $addr): array
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$addr` | **string** |  |





***

### createContext



```php
private createContext(): mixed
```












***

### configureSocket



```php
private configureSocket(): void
```












***

### getLocalAddr



```php
public getLocalAddr(): string
```












***

### close



```php
public close(): void
```












***

### isClosed



```php
public isClosed(): bool
```












***


***
> Automatically generated on 2025-06-28
