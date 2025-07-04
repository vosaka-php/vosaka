***

# ListenerInterface





* Full name: `\venndev\vosaka\net\ListenerInterface`



## Methods


### bind



```php
public static bind(string $addr, array $options = []): \venndev\vosaka\core\Result
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$addr` | **string** |  |
| `$options` | **array** |  |





***

### accept



```php
public accept(float $timeout = 0.0): \venndev\vosaka\core\Result
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$timeout` | **float** |  |





***

### localAddr



```php
public localAddr(): string
```












***

### getOptions



```php
public getOptions(): array
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
> Automatically generated on 2025-07-04
