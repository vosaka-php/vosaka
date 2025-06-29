***

# TCPStream





* Full name: `\venndev\vosaka\net\tcp\TCPStream`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### isClosed



```php
private bool $isClosed
```






***

### bufferSize



```php
private int $bufferSize
```






***

### socket



```php
private mixed $socket
```






***

### peerAddr



```php
private string $peerAddr
```






***

## Methods


### __construct



```php
public __construct(mixed $socket, string $peerAddr): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$socket` | **mixed** |  |
| `$peerAddr` | **string** |  |





***

### read

Read data from stream

```php
public read(int|null $maxBytes = null): \venndev\vosaka\core\Result&lt;string|null&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$maxBytes` | **int&#124;null** | Maximum bytes to read, null for default buffer size |


**Return Value:**

Data read from stream, or null if closed




***

### readExact

Read exact number of bytes

```php
public readExact(int $bytes): \venndev\vosaka\core\Result&lt;string&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$bytes` | **int** | Number of bytes to read |


**Return Value:**

Data read from stream




***

### readUntil

Read until delimiter

```php
public readUntil(string $delimiter): \venndev\vosaka\core\Result&lt;string|null&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$delimiter` | **string** | Delimiter to read until |


**Return Value:**

Data read until delimiter, or null if closed




***

### readLine

Read line (until \n)

```php
public readLine(): \venndev\vosaka\core\Result&lt;string|null&gt;
```









**Return Value:**

Line read from stream, or null if closed




***

### write

Write data to stream

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

Write all data (ensures complete write)

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

Flush the stream

```php
public flush(): \venndev\vosaka\core\Result&lt;void&gt;
```












***

### peerAddr

Get peer address

```php
public peerAddr(): string
```












***

### close

Close the stream

```php
public close(): void
```












***

### isClosed



```php
public isClosed(): bool
```












***

### split

Split stream into reader and writer

```php
public split(): array
```












***


***
> Automatically generated on 2025-06-29
