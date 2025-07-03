***

# TCPListener





* Full name: `\venndev\vosaka\net\tcp\TCPListener`
* Parent class: [`\venndev\vosaka\net\SocketBase`](../SocketBase.md)
* This class is marked as **final** and can't be subclassed
* This class implements:
[`\venndev\vosaka\net\ListenerInterface`](../ListenerInterface.md)
* This class is a **Final class**



## Properties


### isListening



```php
private bool $isListening
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



```php
public static bind(string $addr, array $options = []): \venndev\vosaka\core\Result
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$addr` | **string** |  |
| `$options` | **array** |  |





***

### bindSocket



```php
private bindSocket(): \venndev\vosaka\core\Result
```












***

### accept



```php
public accept(float $timeout = 0.0): \venndev\vosaka\core\Result
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$timeout` | **float** |  |





***

### localAddr



```php
public localAddr(): string
```












***

### getOptions



```php
public getOptions(): array
```












***

### isReusePortEnabled



```php
public isReusePortEnabled(): bool
```












***

### getSocket



```php
public getSocket(): mixed
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
