***

# TCPListener





* Full name: `\venndev\vosaka\net\tcp\TCPListener`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### socket



```php
private mixed $socket
```






***

### isListening



```php
private bool $isListening
```






***

### options



```php
private array $options
```






***

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

## Methods


### __construct



```php
private __construct(string $host, int $port, array $options = []): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$host` | **string** |  |
| `$port` | **int** |  |
| `$options` | **array** |  |





***

### bind

Create a new TCP listener

```php
public static bind(string $addr, array $options = []): \venndev\vosaka\core\Result&lt;\venndev\vosaka\net\tcp\TCPListener&gt;
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$addr` | **string** | Address in &#039;host:port&#039; format |
| `$options` | **array** | Additional options like &#039;ssl&#039;, &#039;ssl_cert&#039;, &#039;ssl_key&#039; |





***

### bindSocket

Bind the socket to the specified address and port

```php
private bindSocket(): \venndev\vosaka\core\Result&lt;void&gt;
```












***

### createContext



```php
private createContext(): mixed
```












***

### accept

Accept incoming connections

```php
public accept(): \venndev\vosaka\core\Result&lt;\venndev\vosaka\net\tcp\TCPStream&gt;
```












***

### localAddr

Get local address

```php
public localAddr(): string
```












***

### close

Close the listener

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
