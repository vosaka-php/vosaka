***

# TCPReadHalf





* Full name: `\venndev\vosaka\net\tcp\TCPReadHalf`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### stream



```php
private \venndev\vosaka\net\tcp\TCPStream $stream
```






***

## Methods


### __construct



```php
public __construct(\venndev\vosaka\net\tcp\TCPStream $stream): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$stream` | **\venndev\vosaka\net\tcp\TCPStream** |  |





***

### read

Read data from the TCP stream

```php
public read(int|null $maxBytes = null): \venndev\vosaka\core\Result&lt;string|null&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$maxBytes` | **int&#124;null** | Maximum number of bytes to read, or null for no limit |


**Return Value:**

Data read from the stream, or null if the stream is closed




***

### readExact

Read exact number of bytes from the TCP stream

```php
public readExact(int $bytes): \venndev\vosaka\core\Result&lt;string&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$bytes` | **int** | Number of bytes to read |


**Return Value:**

Data read from the stream




***

### readUntil

Write data to the TCP stream

```php
public readUntil(string $delimiter): \venndev\vosaka\core\Result&lt;int&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$delimiter` | **string** |  |


**Return Value:**

Number of bytes written




***

### readLine

Read a line from the TCP stream

```php
public readLine(): \venndev\vosaka\core\Result&lt;string|null&gt;
```









**Return Value:**

Line read from the stream, or null if closed




***

### peerAddr



```php
public peerAddr(): string
```












***


***
> Automatically generated on 2025-06-29
