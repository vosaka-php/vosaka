***

# StreamBase





* Full name: `\venndev\vosaka\net\StreamBase`
* Parent class: [`\venndev\vosaka\net\SocketBase`](./SocketBase.md)
* This class implements:
[`\venndev\vosaka\net\StreamInterface`](./StreamInterface.md)
* This class is an **Abstract class**



## Properties


### isClosed



```php
protected bool $isClosed
```






***

### readBuffer



```php
protected string $readBuffer
```






***

### writeBuffer



```php
protected string $writeBuffer
```






***

### writeRegistered



```php
protected bool $writeRegistered
```






***

### bufferSize



```php
protected int $bufferSize
```






***

## Methods


### read



```php
public read(?int $maxBytes = null): \venndev\vosaka\core\Result
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$maxBytes` | **?int** |  |





***

### extractFromBuffer



```php
private extractFromBuffer(?int $maxBytes): string
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$maxBytes` | **?int** |  |





***

### extractUntilDelimiter



```php
private extractUntilDelimiter(string $delimiter): string
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$delimiter` | **string** |  |





***

### readExact



```php
public readExact(int $bytes): \venndev\vosaka\core\Result
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$bytes` | **int** |  |





***

### readUntil



```php
public readUntil(string $delimiter): \venndev\vosaka\core\Result
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$delimiter` | **string** |  |





***

### readLine



```php
public readLine(): \venndev\vosaka\core\Result
```












***

### write



```php
public write(string $data): \venndev\vosaka\core\Result
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$data` | **string** |  |





***

### writeAll



```php
public writeAll(string $data): \venndev\vosaka\core\Result
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$data` | **string** |  |





***

### flush



```php
public flush(): \venndev\vosaka\core\Result
```












***

### handleRead



```php
public handleRead(): void
```




* This method is **abstract**.







***

### handleWrite



```php
public handleWrite(): void
```




* This method is **abstract**.







***

### peerAddr



```php
public peerAddr(): string
```




* This method is **abstract**.







***

### isClosed



```php
public isClosed(): bool
```












***

### close



```php
public close(): void
```












***


## Inherited methods


### createContext



```php
protected static createContext(array $options = []): resource
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$options` | **array** |  |





***

### applySocketOptions



```php
protected static applySocketOptions(mixed $socket, array $options): void
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$socket` | **mixed** |  |
| `$options` | **array** |  |





***

### validatePath



```php
protected static validatePath(string $path): void
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$path` | **string** |  |





***

### parseAddr



```php
protected static parseAddr(string $addr): array
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$addr` | **string** |  |





***

### addToEventLoop



```php
protected static addToEventLoop(mixed $socket): void
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$socket` | **mixed** |  |





***

### removeFromEventLoop



```php
protected static removeFromEventLoop(mixed $socket): void
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$socket` | **mixed** |  |





***

### normalizeOptions

Normalizes the provided socket options.

```php
protected static normalizeOptions(array|\venndev\vosaka\net\option\SocketOptions|null $options = null): array
```

If an instance of SocketOptions is provided, it converts it to an array.
If an array is provided, it merges it with the default options.
If no options are provided, it returns the default socket options.

* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$options` | **array&#124;\venndev\vosaka\net\option\SocketOptions&#124;null** |  |





***


***
> Automatically generated on 2025-07-16
