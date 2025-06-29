***

# UnixListener





* Full name: `\venndev\vosaka\net\unix\UnixListener`
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

### path



```php
private string $path
```






***

## Methods


### __construct



```php
private __construct(string $path, array $options = []): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** |  |
| `$options` | **array** |  |





***

### bind

Create a new Unix domain socket listener

```php
public static bind(string $path, array $options = []): \venndev\vosaka\core\Result&lt;\venndev\vosaka\net\unix\UnixListener&gt;
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | Path to the Unix domain socket |
| `$options` | **array** | Additional options like &#039;reuseaddr&#039;, &#039;backlog&#039; |





***

### bindSocket

Bind the socket to the specified path

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
public accept(): \venndev\vosaka\core\Result&lt;\venndev\vosaka\net\unix\UnixStream&gt;
```












***

### localPath

Get local path

```php
public localPath(): string
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

### validatePath

Validate Unix domain socket path.

```php
private static validatePath(string $path): void
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** | Path to validate |




**Throws:**
<p>If path is invalid</p>

- [`InvalidArgumentException`](../../../../InvalidArgumentException.md)



***


***
> Automatically generated on 2025-06-29
