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
| `$options` | **array** | Additional options like &#039;ssl&#039;, &#039;reuseport&#039;, etc. |





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

### applySocketOptions



```php
private applySocketOptions(): void
```












***

### logSocketOptions

Log enabled socket options for debugging purposes.

```php
private logSocketOptions(): void
```












***

### accept

Accept incoming connections.

```php
public accept(float $timeout = 0.0): \venndev\vosaka\core\Result&lt;\venndev\vosaka\net\tcp\TCPStream|null&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$timeout` | **float** | Connection timeout (currently unused) |


**Return Value:**

A Result containing the TCPStream on success, null if no connections



**Throws:**
<p>If listener is not bound</p>

- [`InvalidArgumentException`](../../../../InvalidArgumentException.md)



***

### localAddr

Get local address

```php
public localAddr(): string
```












***

### getOptions

Get socket options info

```php
public getOptions(): array
```












***

### isReusePortEnabled

Check if SO_REUSEPORT is enabled

```php
public isReusePortEnabled(): bool
```












***

### getSocket

Get socket resource (for advanced usage)

```php
public getSocket(): mixed
```












***

### close

Close the listener

```php
public close(): void
```












***

### setTcpNodelay

Set TCP_NODELAY with cross-platform compatibility

```php
private setTcpNodelay(mixed $socket): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$socket` | **mixed** |  |





***

### isClosed



```php
public isClosed(): bool
```












***


***
> Automatically generated on 2025-07-02
