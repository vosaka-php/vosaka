***

# SrvRecord





* Full name: `\venndev\vosaka\net\dns\model\SrvRecord`
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

### priority



```php
public int $priority
```






***

### weight



```php
public int $weight
```






***

### port



```php
public int $port
```






***

### target



```php
public string $target
```






***

## Methods


### __construct



```php
public __construct(bool $success, ?string $error, int $priority, int $weight, int $port, string $target): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$success` | **bool** |  |
| `$error` | **?string** |  |
| `$priority` | **int** |  |
| `$weight` | **int** |  |
| `$port` | **int** |  |
| `$target` | **string** |  |





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

### getPriority

Get the SRV priority

```php
public getPriority(): int
```












***

### getWeight

Get the SRV weight

```php
public getWeight(): int
```












***

### getPort

Get the SRV port

```php
public getPort(): int
```












***

### getTarget

Get the SRV target hostname

```php
public getTarget(): string
```












***

### hasHigherPriorityThan

Check if this is a higher priority than another SRV record
(lower priority value means higher priority)

```php
public hasHigherPriorityThan(\venndev\vosaka\net\dns\model\SrvRecord $other): bool
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$other` | **\venndev\vosaka\net\dns\model\SrvRecord** |  |





***

### hasLowerPriorityThan

Check if this is a lower priority than another SRV record
(higher priority value means lower priority)

```php
public hasLowerPriorityThan(\venndev\vosaka\net\dns\model\SrvRecord $other): bool
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$other` | **\venndev\vosaka\net\dns\model\SrvRecord** |  |





***

### hasSamePriorityAs

Check if this has the same priority as another SRV record

```php
public hasSamePriorityAs(\venndev\vosaka\net\dns\model\SrvRecord $other): bool
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$other` | **\venndev\vosaka\net\dns\model\SrvRecord** |  |





***

### getEndpoint

Get the service endpoint as host:port

```php
public getEndpoint(): string
```












***

### isNullTarget

Check if the target is a null target (.)

```php
public isNullTarget(): bool
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

String representation

```php
public __toString(): string
```












***

### compare

Compare two SRV records for sorting
Returns negative if this record should come first, positive if second, 0 if equal

```php
public compare(\venndev\vosaka\net\dns\model\SrvRecord $other): int
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$other` | **\venndev\vosaka\net\dns\model\SrvRecord** |  |





***


***
> Automatically generated on 2025-07-04
