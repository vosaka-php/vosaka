***

# Constants





* Full name: `\venndev\vosaka\core\Constants`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**


## Constants

| Constant | Visibility | Type | Value |
|:---------|:-----------|:-----|:------|
|`SIGHUP`|public| |1|
|`SIGINT`|public| |2|
|`SIGQUIT`|public| |3|
|`SIGILL`|public| |4|
|`SIGTRAP`|public| |5|
|`SIGABRT`|public| |6|
|`SIGBUS`|public| |7|
|`SIGFPE`|public| |8|
|`SIGKILL`|public| |9|
|`SIGUSR1`|public| |10|
|`SIGSEGV`|public| |11|
|`SIGUSR2`|public| |12|
|`SIGPIPE`|public| |13|
|`SIGALRM`|public| |14|
|`SIGTERM`|public| |15|
|`SIGCHLD`|public| |17|
|`SIGCONT`|public| |18|
|`SIGSTOP`|public| |19|
|`SIGTSTP`|public| |20|
|`SIGTTIN`|public| |21|
|`SIGTTOU`|public| |22|
|`PHP_WINDOWS_EVENT_CTRL_C`|public| |0|
|`PHP_WINDOWS_EVENT_CTRL_BREAK`|public| |1|
|`WNOHANG`|public| |1|
|`WUNTRACED`|public| |2|
|`S_IRUSR`|public| |0400|
|`S_IWUSR`|public| |0200|
|`S_IXUSR`|public| |0100|
|`S_IRGRP`|public| |040|
|`S_IWGRP`|public| |020|
|`S_IXGRP`|public| |010|
|`S_IROTH`|public| |04|
|`S_IWOTH`|public| |02|
|`S_IXOTH`|public| |01|
|`FILE_PERM_READ_ONLY`|public| |0444|
|`FILE_PERM_READ_WRITE`|public| |0644|
|`FILE_PERM_EXECUTABLE`|public| |0755|
|`FILE_PERM_FULL`|public| |0777|
|`FATAL_ERROR_TYPES`|public| |[E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR]|
|`MEMORY_NORMAL_THRESHOLD`|public| |0.6|
|`MEMORY_WARNING_THRESHOLD`|public| |0.75|
|`MEMORY_CRITICAL_THRESHOLD`|public| |0.85|
|`MEMORY_EMERGENCY_THRESHOLD`|public| |0.95|
|`PRIORITY_HIGH`|public| |-10|
|`PRIORITY_NORMAL`|public| |0|
|`PRIORITY_LOW`|public| |10|
|`PRIORITY_IDLE`|public| |19|
|`TIMEOUT_SHORT`|public| |5|
|`TIMEOUT_MEDIUM`|public| |30|
|`TIMEOUT_LONG`|public| |300|
|`TIMEOUT_VERY_LONG`|public| |3600|
|`BUFFER_SIZE_SMALL`|public| |1024|
|`BUFFER_SIZE_MEDIUM`|public| |8192|
|`BUFFER_SIZE_LARGE`|public| |65536|
|`BUFFER_SIZE_HUGE`|public| |1048576|
|`DEFAULT_TCP_PORT`|public| |8080|
|`DEFAULT_UDP_PORT`|public| |8081|
|`MAX_CONNECTIONS`|public| |1000|
|`SOCKET_TIMEOUT`|public| |30|


## Methods


### getSignal

Get a signal constant value with fallback

```php
public static getSignal(string $signalName): int
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$signalName` | **string** |  |





***

### getWindowsEvent

Get a Windows event constant with fallback

```php
public static getWindowsEvent(string $eventName): int
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$eventName` | **string** |  |





***

### isWindows

Check if we're running on Windows

```php
public static isWindows(): bool
```



* This method is **static**.








***

### isUnix

Check if we're running on Unix-like system

```php
public static isUnix(): bool
```



* This method is **static**.








***

### hasPcntl

Check if PCNTL extension is available

```php
public static hasPcntl(): bool
```



* This method is **static**.








***

### hasPosix

Check if POSIX extension is available

```php
public static hasPosix(): bool
```



* This method is **static**.








***

### getWaitFlag

Get the appropriate wait flag with fallback

```php
public static getWaitFlag(string $flagName): int
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$flagName` | **string** |  |





***

### getSafeSignal

Get safe signal value for the current platform

```php
public static getSafeSignal(string $signalName): ?int
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$signalName` | **string** |  |





***

### getSafeWindowsEvent

Get safe Windows event value for the current platform

```php
public static getSafeWindowsEvent(string $eventName): ?int
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$eventName` | **string** |  |





***

### getNullDevice



```php
private static getNullDevice(): string
```



* This method is **static**.








***


***
> Automatically generated on 2025-07-04
