***

# StreamInterface

Extended interface for stream-based connections



* Full name: `\venndev\vosaka\net\contracts\StreamInterface`
* Parent interfaces: [`\venndev\vosaka\net\contracts\ConnectionInterface`](./ConnectionInterface.md)


## Methods


### readLine

Read a line (until \n)

```php
public readLine(): \venndev\vosaka\core\Result&lt;string&gt;
```












***

### readUntil

Read until delimiter is found

```php
public readUntil(string $delimiter): \venndev\vosaka\core\Result&lt;string&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$delimiter` | **string** |  |





***

### readExact

Read exact number of bytes

```php
public readExact(int $bytes): \venndev\vosaka\core\Result&lt;string&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$bytes` | **int** |  |





***

### writeAll

Write all data, retrying if necessary

```php
public writeAll(string $data): \venndev\vosaka\core\Result&lt;void&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$data` | **string** |  |





***

### flush

Flush write buffer

```php
public flush(): \venndev\vosaka\core\Result&lt;void&gt;
```












***

### readable

Check if data is available for reading

```php
public readable(): bool
```












***

### writable

Check if connection is ready for writing

```php
public writable(): bool
```












***


## Inherited methods


### read

Read data from the connection

```php
public read(int $length = -1): \venndev\vosaka\core\Result&lt;string&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$length` | **int** | Maximum bytes to read, -1 for all available |





***

### write

Write data to the connection

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

### close

Close the connection

```php
public close(): void
```












***

### isClosed

Check if connection is closed

```php
public isClosed(): bool
```












***

### getLocalAddress

Get local address

```php
public getLocalAddress(): \venndev\vosaka\net\contracts\AddressInterface
```












***

### getRemoteAddress

Get remote address

```php
public getRemoteAddress(): \venndev\vosaka\net\contracts\AddressInterface
```












***

### setReadTimeout

Set read timeout in seconds

```php
public setReadTimeout(float $seconds): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$seconds` | **float** |  |





***

### setWriteTimeout

Set write timeout in seconds

```php
public setWriteTimeout(float $seconds): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$seconds` | **float** |  |





***


***
> Automatically generated on 2025-07-24
