***

# SocketInterface

Low-level socket interface



* Full name: `\venndev\vosaka\net\contracts\SocketInterface`



## Methods


### getResource

Get the underlying socket resource

```php
public getResource(): resource
```












***

### setOption

Set socket option

```php
public setOption(int $level, int $option, mixed $value): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$level` | **int** |  |
| `$option` | **int** |  |
| `$value` | **mixed** |  |





***

### getOption

Get socket option

```php
public getOption(int $level, int $option): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$level` | **int** |  |
| `$option` | **int** |  |





***

### setBlocking

Set blocking mode

```php
public setBlocking(bool $blocking): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$blocking` | **bool** |  |





***

### shutdown

Shutdown socket

```php
public shutdown(int $how = 2): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$how` | **int** | 0=read, 1=write, 2=both |





***


***
> Automatically generated on 2025-07-24
