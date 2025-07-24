***

# EventLoopIntegration

Event loop integration for sockets



* Full name: `\venndev\vosaka\net\EventLoopIntegration`



## Properties


### readHandlers



```php
private array $readHandlers
```






***

### writeHandlers



```php
private array $writeHandlers
```






***

### errorHandlers



```php
private array $errorHandlers
```






***

## Methods


### onReadable

Register read handler

```php
public onReadable(mixed $socket, callable $handler): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$socket` | **mixed** |  |
| `$handler` | **callable** |  |





***

### onWritable

Register write handler

```php
public onWritable(mixed $socket, callable $handler): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$socket` | **mixed** |  |
| `$handler` | **callable** |  |





***

### removeReadable

Remove read handler

```php
public removeReadable(mixed $socket): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$socket` | **mixed** |  |





***

### removeWritable

Remove write handler

```php
public removeWritable(mixed $socket): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$socket` | **mixed** |  |





***

### removeAll

Remove all handlers

```php
public removeAll(mixed $socket): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$socket` | **mixed** |  |





***


***
> Automatically generated on 2025-07-24
