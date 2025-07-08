***

# StreamOptions





* Full name: `\venndev\vosaka\net\option\StreamOptions`
* This class is marked as **final** and can't be subclassed
* This class implements:
[`\venndev\vosaka\net\option\StreamOptionsInterface`](./StreamOptionsInterface.md)
* This class is a **Final class**



## Properties


### options



```php
private array $options
```






***

## Methods


### __construct



```php
public __construct(array $options = []): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$options` | **array** |  |





***

### setTimeout



```php
public setTimeout(int $seconds, int $microseconds): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$seconds` | **int** |  |
| `$microseconds` | **int** |  |





***

### setBufferSize



```php
public setBufferSize(int $size): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$size` | **int** |  |





***

### setBlocking



```php
public setBlocking(bool $blocking): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$blocking` | **bool** |  |





***

### setReadTimeout



```php
public setReadTimeout(int $seconds, int $microseconds): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$seconds` | **int** |  |
| `$microseconds` | **int** |  |





***

### setWriteTimeout



```php
public setWriteTimeout(int $seconds, int $microseconds): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$seconds` | **int** |  |
| `$microseconds` | **int** |  |





***

### setChunkSize



```php
public setChunkSize(int $size): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$size` | **int** |  |





***

### toArray



```php
public toArray(): array
```












***

### merge



```php
public merge(\venndev\vosaka\net\option\StreamOptionsInterface $other): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$other` | **\venndev\vosaka\net\option\StreamOptionsInterface** |  |





***


***
> Automatically generated on 2025-07-08
