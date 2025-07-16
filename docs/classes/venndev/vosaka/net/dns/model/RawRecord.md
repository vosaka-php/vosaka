***

# RawRecord





* Full name: `\venndev\vosaka\net\dns\model\RawRecord`
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

### raw



```php
public string $raw
```






***

## Methods


### __construct



```php
public __construct(bool $success, ?string $error, string $raw): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$success` | **bool** |  |
| `$error` | **?string** |  |
| `$raw` | **string** |  |





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

### getRaw

Get the raw hexadecimal data

```php
public getRaw(): string
```












***

### getBinary

Get the raw data as binary

```php
public getBinary(): string
```












***

### getLength

Get the length of the raw data in bytes

```php
public getLength(): int
```












***

### getBytes

Get the raw data as an array of bytes

```php
public getBytes(): array
```












***

### getHexDump

Get formatted hex dump of the raw data

```php
public getHexDump(): string
```












***

### isEmpty

Check if the raw data is empty

```php
public isEmpty(): bool
```












***

### contains

Search for a pattern in the raw hex data

```php
public contains(string $hexPattern): bool
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$hexPattern` | **string** |  |





***

### extract

Extract a portion of the raw data

```php
public extract(int $start, int $length = null): string
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$start` | **int** |  |
| `$length` | **int** |  |





***

### getAsText

Try to interpret raw data as printable text

```php
public getAsText(): string
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

### fromBinary

Create from binary data

```php
public static fromBinary(string $binary, bool $success = true, ?string $error = null): self
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$binary` | **string** |  |
| `$success` | **bool** |  |
| `$error` | **?string** |  |





***

### __toString

String representation

```php
public __toString(): string
```












***

### toDebugString

Debug representation with hex dump

```php
public toDebugString(): string
```












***


***
> Automatically generated on 2025-07-16
