***

# ConnectionInterface

Base interface for all network connections



* Full name: `\venndev\vosaka\net\contracts\ConnectionInterface`



## Methods


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
