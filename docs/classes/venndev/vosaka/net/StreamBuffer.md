***

# StreamBuffer

Stream buffer for managing read/write data



* Full name: `\venndev\vosaka\net\StreamBuffer`



## Properties


### data



```php
private string $data
```






***

### size



```php
private int $size
```






***

### maxSize



```php
private int $maxSize
```






***

## Methods


### __construct



```php
public __construct(int $maxSize = 1048576): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$maxSize` | **int** |  |





***

### append

Set maximum buffer size

```php
public append(string $data): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$data` | **string** |  |





***

### read

Read data from the buffer

```php
public read(int $length = -1): string
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$length` | **int** | Number of bytes to read, -1 for all |


**Return Value:**

Data read from the buffer




***

### readUntil

Read data until a specific delimiter

```php
public readUntil(string $delimiter): string|null
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$delimiter` | **string** | Delimiter to read until |


**Return Value:**

Data read until the delimiter, or null if not found




***

### readLine

Read a line from the buffer

```php
public readLine(): string|null
```









**Return Value:**

Line read from the buffer, or null if no line found




***

### peek

Peek data in the buffer without removing it

```php
public peek(int $length = -1): string
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$length` | **int** | Number of bytes to peek, -1 for all |


**Return Value:**

Data peeked from the buffer




***

### isEmpty

Check if the buffer is empty

```php
public isEmpty(): bool
```









**Return Value:**

True if the buffer is empty, false otherwise




***

### getSize

Get the current size of the buffer

```php
public getSize(): int
```









**Return Value:**

Size of the buffer in bytes




***

### clear

Clear the buffer

```php
public clear(): void
```












***


***
> Automatically generated on 2025-07-24
