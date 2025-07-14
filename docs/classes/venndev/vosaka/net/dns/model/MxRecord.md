***

# MxRecord





* Full name: `\venndev\vosaka\net\dns\model\MxRecord`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### success



```php
public bool $success
```






***

### error



```php
public ?string $error
```






***

### preference



```php
public int $preference
```






***

### exchange



```php
public string $exchange
```






***

## Methods


### __construct



```php
public __construct(bool $success, ?string $error, int $preference, string $exchange): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$success` | **bool** |  |
| `$error` | **?string** |  |
| `$preference` | **int** |  |
| `$exchange` | **string** |  |





***

### isSuccess

Check if the record parsing was successful

```php
public isSuccess(): bool
```












***

### getError

Get error message if parsing failed

```php
public getError(): ?string
```












***

### getPreference

Get the MX preference (priority)

```php
public getPreference(): int
```












***

### getExchange

Get the MX exchange (mail server hostname)

```php
public getExchange(): string
```












***

### getPriority

Get priority (alias for preference)

```php
public getPriority(): int
```












***

### getMailServer

Get mail server hostname (alias for exchange)

```php
public getMailServer(): string
```












***

### toArray

Convert to array format for backwards compatibility

```php
public toArray(): array
```












***

### fromArray

Create from array data

```php
public static fromArray(array $data): self
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$data` | **array** |  |





***

### __toString

String representation showing preference and exchange

```php
public __toString(): string
```












***


***
> Automatically generated on 2025-07-14
