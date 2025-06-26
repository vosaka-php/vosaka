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

### socketResource



```php
private mixed $socketResource
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
public __construct(mixed $socketResource, string $path): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$socketResource` | **mixed** |  |
| `$path` | **string** |  |





***

### accept

Accept incoming connections

```php
public accept(): \venndev\vosaka\core\Result&lt;\venndev\vosaka\net\unix\UnixStream&gt;
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
