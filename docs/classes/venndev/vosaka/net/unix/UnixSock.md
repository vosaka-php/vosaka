***

# UnixSock





* Full name: `\venndev\vosaka\net\unix\UnixSock`
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

### path



```php
private string $path
```






***

### options



```php
private array $options
```






***

## Methods


### __construct



```php
private __construct(): mixed
```












***

### new



```php
public static new(): self
```



* This method is **static**.








***

### bind

Bind the socket to the specified Unix domain socket path

```php
public bind(string $path): \venndev\vosaka\core\Result&lt;\venndev\vosaka\net\unix\UnixSock&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | Path to the Unix socket file |





***

### listen

Listen for incoming connections on the bound Unix socket

```php
public listen(int $backlog = SOMAXCONN): \venndev\vosaka\core\Result&lt;\venndev\vosaka\net\unix\UnixListener&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$backlog` | **int** | Maximum number of pending connections |





***

### connect

Connect to a Unix domain socket

```php
public connect(string $path): \venndev\vosaka\core\Result&lt;\venndev\vosaka\net\unix\UnixStream&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | Path to the Unix socket file |





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

### validatePath



```php
private validatePath(string $path): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** |  |





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

### getLocalPath



```php
public getLocalPath(): string
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
> Automatically generated on 2025-06-26
