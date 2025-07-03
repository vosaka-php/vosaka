***

# TCPSock





* Full name: `\venndev\vosaka\net\tcp\TCPSock`
* Parent class: [`\venndev\vosaka\net\SocketBase`](../SocketBase.md)
* This class is marked as **final** and can't be subclassed
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



```php
public bind(string $addr): \venndev\vosaka\core\Result
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$addr` | **string** |  |





***

### listen



```php
public listen(int $backlog = SOMAXCONN): \venndev\vosaka\core\Result
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$backlog` | **int** |  |





***

### connect



```php
public connect(string $addr): \venndev\vosaka\core\Result
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$addr` | **string** |  |





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

### setKeepAlive



```php
public setKeepAlive(bool $keepAlive): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$keepAlive` | **bool** |  |





***

### setNoDelay



```php
public setNoDelay(bool $noDelay): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$noDelay` | **bool** |  |





***

### setSsl



```php
public setSsl(bool $ssl, ?string $sslCert = null, ?string $sslKey = null): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$ssl` | **bool** |  |
| `$sslCert` | **?string** |  |
| `$sslKey` | **?string** |  |





***

### getLocalAddr



```php
public getLocalAddr(): string
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
