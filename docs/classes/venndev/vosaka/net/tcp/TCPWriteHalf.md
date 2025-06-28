***

# TCPWriteHalf





* Full name: `\venndev\vosaka\net\tcp\TCPWriteHalf`
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

### write

Write data to the TCP stream

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

### writeAll

Write all data to the TCP stream

```php
public writeAll(string $data): \venndev\vosaka\core\Result&lt;int&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$data` | **string** | Data to write |


**Return Value:**

Number of bytes written




***

### flush

Flush the TCP stream

```php
public flush(): \venndev\vosaka\core\Result&lt;void&gt;
```









**Return Value:**

Result indicating success or failure




***

### peerAddr



```php
public peerAddr(): string
```












***


***
> Automatically generated on 2025-06-28
