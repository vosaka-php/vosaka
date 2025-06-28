***

# UnixReadHalf

Read half of a Unix domain socket stream.

This class represents the read-only half of a Unix domain socket stream,
created by splitting a UnixStream. It provides read-only access to the
underlying socket while maintaining the same async interface.

* Full name: `\venndev\vosaka\net\unix\UnixReadHalf`
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
> Automatically generated on 2025-06-28
