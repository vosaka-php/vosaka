***

# Stdio





* Full name: `\venndev\vosaka\process\Stdio`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**




## Methods


### piped

Provides standard input/output/error streams for process execution.

```php
public static piped(): array&lt;int,array&lt;string,mixed&gt;&gt;
```



* This method is **static**.





**Return Value:**

An array defining the standard streams.




***

### null

Provides standard input/output/error streams that are not piped.

```php
public static null(): array&lt;int,array&lt;string,mixed&gt;&gt;
```



* This method is **static**.





**Return Value:**

An array defining the standard streams.




***

### inherit

Provides standard input/output/error streams that inherit from the parent process.

```php
public static inherit(): array&lt;int,mixed&gt;
```



* This method is **static**.





**Return Value:**

An array defining the standard streams.




***

### getNullDevice



```php
private static getNullDevice(): string
```



* This method is **static**.








***

### isWindows

Checks if the current platform is Windows.

```php
public static isWindows(): bool
```



* This method is **static**.





**Return Value:**

Returns true if the platform is Windows, false otherwise.




***


***
> Automatically generated on 2025-07-08
