***

# SoaRecord





* Full name: `\venndev\vosaka\net\dns\model\SoaRecord`
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

### primary



```php
public string $primary
```






***

### admin



```php
public string $admin
```






***

### serial



```php
public int $serial
```






***

### refresh



```php
public int $refresh
```






***

### retry



```php
public int $retry
```






***

### expire



```php
public int $expire
```






***

### minimum



```php
public int $minimum
```






***

## Methods


### __construct



```php
public __construct(bool $success, ?string $error, string $primary, string $admin, int $serial, int $refresh, int $retry, int $expire, int $minimum): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$success` | **bool** |  |
| `$error` | **?string** |  |
| `$primary` | **string** |  |
| `$admin` | **string** |  |
| `$serial` | **int** |  |
| `$refresh` | **int** |  |
| `$retry` | **int** |  |
| `$expire` | **int** |  |
| `$minimum` | **int** |  |





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

### getPrimary

Get the primary name server

```php
public getPrimary(): string
```












***

### getAdmin

Get the administrator email address

```php
public getAdmin(): string
```












***

### getSerial

Get the serial number

```php
public getSerial(): int
```












***

### getRefresh

Get the refresh interval in seconds

```php
public getRefresh(): int
```












***

### getRetry

Get the retry interval in seconds

```php
public getRetry(): int
```












***

### getExpire

Get the expire time in seconds

```php
public getExpire(): int
```












***

### getMinimum

Get the minimum TTL in seconds

```php
public getMinimum(): int
```












***

### getAdminEmail

Get the administrator email in standard format

```php
public getAdminEmail(): string
```












***

### getRefreshFormatted

Get refresh interval in human readable format

```php
public getRefreshFormatted(): string
```












***

### getRetryFormatted

Get retry interval in human readable format

```php
public getRetryFormatted(): string
```












***

### getExpireFormatted

Get expire time in human readable format

```php
public getExpireFormatted(): string
```












***

### getMinimumFormatted

Get minimum TTL in human readable format

```php
public getMinimumFormatted(): string
```












***

### formatTime

Format time in seconds to human readable format

```php
private formatTime(int $seconds): string
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$seconds` | **int** |  |





***

### isNewerThan

Check if the serial number indicates a newer version than another SOA record

```php
public isNewerThan(\venndev\vosaka\net\dns\model\SoaRecord $other): bool
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$other` | **\venndev\vosaka\net\dns\model\SoaRecord** |  |





***

### isOlderThan

Check if this SOA record is older than another

```php
public isOlderThan(\venndev\vosaka\net\dns\model\SoaRecord $other): bool
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$other` | **\venndev\vosaka\net\dns\model\SoaRecord** |  |





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
> Automatically generated on 2025-06-29
