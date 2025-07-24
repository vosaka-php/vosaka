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

Cleans the output by trimming whitespace and quotes.

```php
public static clean(string $output): string
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$output` | **string** | The output to clean. |


**Return Value:**

The cleaned output.




***

### cleanAdvanced

Cleans the output with advanced options.

```php
public static cleanAdvanced(string $output, array $options = []): string
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$output` | **string** | The output to clean. |
| `$options` | **array** | Options for cleaning:<br />- &#039;remove_quotes&#039;: Whether to remove quotes (default: true).<br />- &#039;trim_whitespace&#039;: Whether to trim whitespace (default: true).<br />- &#039;remove_extra_newlines&#039;: Whether to remove extra newlines (default: false).<br />- &#039;encoding&#039;: Encoding to convert the output to (optional).<br />- &#039;normalize_line_endings&#039;: Whether to normalize line endings (default: false). |


**Return Value:**

The cleaned output.




***

### cleanLines

Cleans the output and splits it into lines.

```php
public static cleanLines(string $output): array
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$output` | **string** | The output to clean. |


**Return Value:**

An array of cleaned lines.




***

### cleanJson

Cleans the output and decodes it as JSON.

```php
public static cleanJson(string $output): array|string
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$output` | **string** | The output to clean and decode. |


**Return Value:**

The decoded JSON as an associative array, or the cleaned string if decoding fails.




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
> Automatically generated on 2025-07-24
