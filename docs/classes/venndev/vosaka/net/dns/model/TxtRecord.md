***

# TxtRecord





* Full name: `\venndev\vosaka\net\dns\model\TxtRecord`
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

### text



```php
public string $text
```






***

### segments



```php
public array $segments
```






***

## Methods


### __construct



```php
public __construct(bool $success, ?string $error, string $text, array $segments): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$success` | **bool** |  |
| `$error` | **?string** |  |
| `$text` | **string** |  |
| `$segments` | **array** |  |





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

### getText

Get the complete TXT record content

```php
public getText(): string
```












***

### getSegments

Get the individual TXT record segments

```php
public getSegments(): array
```












***

### getSegmentCount

Get the number of TXT segments

```php
public getSegmentCount(): int
```












***

### contains

Check if TXT record contains specific text

```php
public contains(string $needle): bool
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$needle` | **string** |  |





***

### startsWith

Check if TXT record starts with specific text

```php
public startsWith(string $prefix): bool
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$prefix` | **string** |  |





***

### getLength

Get TXT record length

```php
public getLength(): int
```












***

### split

Split TXT record by delimiter

```php
public split(string $delimiter = &#039; &#039;): array
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$delimiter` | **string** |  |





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

String representation

```php
public __toString(): string
```












***


***
> Automatically generated on 2025-07-16
