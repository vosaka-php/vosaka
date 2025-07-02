***

# UnixWriteHalf

Write half of a Unix domain socket stream.

This class represents the write-only half of a Unix domain socket stream,
created by splitting a UnixStream. It provides write-only access to the
underlying socket while maintaining the same async interface.

* Full name: `\venndev\vosaka\net\unix\UnixWriteHalf`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### stream



```php
private \venndev\vosaka\net\unix\UnixStream $stream
```






***

## Methods


### __construct



```php
public __construct(\venndev\vosaka\net\unix\UnixStream $stream): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$stream` | **\venndev\vosaka\net\unix\UnixStream** |  |





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

### peerPath

Get peer path

```php
public peerPath(): string
```












***

### localPath

Get local path

```php
public localPath(): string
```












***

### isClosed

Check if stream is closed

```php
public isClosed(): bool
```












***


***
> Automatically generated on 2025-07-02
