***

# UnixStream





* Full name: `\venndev\vosaka\net\unix\UnixStream`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### socket



```php
private mixed $socket
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
public __construct(mixed $socket, string $path): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$socket` | **mixed** |  |
| `$path` | **string** |  |





***

### read

Read data from the stream

```php
public read(int $length): \venndev\vosaka\core\Result&lt;string&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$length` | **int** | Number of bytes to read |


**Return Value:**

Data read from the stream




***

### write

Write data to the stream

```php
public write(string $data): \venndev\vosaka\core\Result&lt;int&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$data` | **string** | Data to write |


**Return Value:**

Number of bytes written




***

### getPeerPath



```php
public getPeerPath(): string
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
