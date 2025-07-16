***

# DatagramInterface





* Full name: `\venndev\vosaka\net\DatagramInterface`



## Methods


### sendTo



```php
public sendTo(string $data, string $addr): \venndev\vosaka\core\Result
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$data` | **string** |  |
| `$addr` | **string** |  |





***

### receiveFrom



```php
public receiveFrom(int $maxLength = 65535): \venndev\vosaka\core\Result
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$maxLength` | **int** |  |





***

### getLocalAddr



```php
public getLocalAddr(): string
```












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


***
> Automatically generated on 2025-07-16
