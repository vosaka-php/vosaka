***

# ProcOC





* Full name: `\venndev\vosaka\process\ProcOC`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**


## Constants

| Constant | Visibility | Type | Value |
|:---------|:-----------|:-----|:------|
|`REMOVE_QUOTES`|public| |&quot;remove_quotes&quot;|
|`TRIM_WHITESPACE`|public| |&quot;trim_whitespace&quot;|
|`REMOVE_EXTRA_NEWLINES`|public| |&quot;remove_extra_newlines&quot;|
|`ENCODING`|public| |&quot;encoding&quot;|
|`NORMALIZE_LINE_ENDINGS`|public| |&quot;normalize_line_endings&quot;|


## Methods


### clean



```php
public static clean(string $output): string
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$output` | **string** |  |





***

### cleanAdvanced



```php
public static cleanAdvanced(string $output, array $options = []): string
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$output` | **string** |  |
| `$options` | **array** |  |





***

### cleanLines



```php
public static cleanLines(string $output): array
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$output` | **string** |  |





***

### cleanJson



```php
public static cleanJson(string $output): array|string
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$output` | **string** |  |





***

### normalizeLineEndings



```php
private static normalizeLineEndings(string $text): string
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$text` | **string** |  |





***


***
> Automatically generated on 2025-07-02
