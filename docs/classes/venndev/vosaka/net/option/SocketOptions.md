***

# SocketOptions





* Full name: `\venndev\vosaka\net\option\SocketOptions`
* This class is marked as **final** and can't be subclassed
* This class implements:
[`\venndev\vosaka\net\option\SocketOptionsInterface`](./SocketOptionsInterface.md)
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

### setReuseAddress



```php
public setReuseAddress(bool $reuse = true): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$reuse` | **bool** |  |





***

### setReusePort



```php
public setReusePort(bool $reuse = true): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$reuse` | **bool** |  |





***

### setTcpNoDelay



```php
public setTcpNoDelay(bool $nodelay = true): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$nodelay` | **bool** |  |





***

### setKeepAlive



```php
public setKeepAlive(bool $keepalive = true): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$keepalive` | **bool** |  |





***

### setSendBufferSize



```php
public setSendBufferSize(int $size): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$size` | **int** |  |





***

### setReceiveBufferSize



```php
public setReceiveBufferSize(int $size): self
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

### setBacklog



```php
public setBacklog(int $backlog): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$backlog` | **int** |  |





***

### setBindAddress



```php
public setBindAddress(string $address): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$address` | **string** |  |





***

### toArray



```php
public toArray(): array
```












***

### merge



```php
public merge(\venndev\vosaka\net\option\SocketOptionsInterface $other): self
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$other` | **\venndev\vosaka\net\option\SocketOptionsInterface** |  |





***


***
> Automatically generated on 2025-07-03
