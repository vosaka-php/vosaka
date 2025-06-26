# VOsaka Constants

A cross-platform constants library for handling system-specific constants that may not be available on all platforms, particularly Windows systems.

## Overview

The `Constants` class provides fallback values for POSIX signals, Windows events, and other system constants that are commonly used in process control and signal handling but may not be defined on all PHP installations.

## Features

- **Cross-platform compatibility** - Works on Windows, Linux, macOS, and other Unix-like systems
- **Safe constant retrieval** - Provides fallback values when system constants are not defined
- **Signal handling** - POSIX signal constants with fallbacks
- **Windows events** - Windows-specific event constants
- **Process control** - Wait flags and process termination signals
- **Memory management** - Predefined memory threshold constants
- **File permissions** - Common file permission constants
- **Network defaults** - Default port and connection constants

## Installation

The Constants class is included with VOsaka. Simply use it in your code:

```php
use venndev\vosaka\core\Constants;
```

## Usage

### Platform Detection

```php
// Check the current platform
if (Constants::isWindows()) {
    echo "Running on Windows";
} elseif (Constants::isUnix()) {
    echo "Running on Unix-like system";
}

// Check for available extensions
if (Constants::hasPcntl()) {
    echo "PCNTL extension is available";
}

if (Constants::hasPosix()) {
    echo "POSIX extension is available";
}
```

### Signal Handling

```php
// Safe signal retrieval with fallbacks
$sigint = Constants::getSafeSignal('SIGINT');   // Returns 2 if SIGINT not defined
$sigterm = Constants::getSafeSignal('SIGTERM'); // Returns 15 if SIGTERM not defined

// Use in signal handlers
if (!Constants::isWindows() && Constants::hasPcntl()) {
    pcntl_signal($sigint, function($signal) {
        echo "Received signal: $signal";
    });
}
```

### Windows Event Handling

```php
// Windows-specific event handling
if (Constants::isWindows()) {
    $ctrlc = Constants::getSafeWindowsEvent('PHP_WINDOWS_EVENT_CTRL_C');

    if (function_exists('sapi_windows_set_ctrl_handler')) {
        sapi_windows_set_ctrl_handler(function($event) use ($ctrlc) {
            if ($event === $ctrlc) {
                echo "Ctrl+C pressed";
            }
        });
    }
}
```

### Process Control

```php
// Terminate processes safely
$sigterm = Constants::getSafeSignal('SIGTERM');
proc_terminate($process, $sigterm);

// Wait for child processes
if (Constants::hasPcntl()) {
    $wnohang = Constants::getWaitFlag('WNOHANG');
    pcntl_waitpid($pid, $status, $wnohang);
}
```

### Memory Management

```php
// Use predefined memory thresholds
$currentUsage = memory_get_usage(true) / memory_get_peak_usage(true);

if ($currentUsage > Constants::MEMORY_CRITICAL_THRESHOLD) {
    echo "Critical memory usage!";
} elseif ($currentUsage > Constants::MEMORY_WARNING_THRESHOLD) {
    echo "High memory usage warning";
}
```

### File Permissions

```php
// Use predefined file permissions
chmod($file, Constants::FILE_PERM_READ_WRITE);  // 0644
chmod($script, Constants::FILE_PERM_EXECUTABLE); // 0755
```

## Available Constants

### Signal Constants

| Constant | Value | Description |
|----------|-------|-------------|
| `SIGHUP` | 1 | Hangup |
| `SIGINT` | 2 | Interrupt (Ctrl+C) |
| `SIGQUIT` | 3 | Quit |
| `SIGILL` | 4 | Illegal instruction |
| `SIGTRAP` | 5 | Trap |
| `SIGABRT` | 6 | Abort |
| `SIGBUS` | 7 | Bus error |
| `SIGFPE` | 8 | Floating point exception |
| `SIGKILL` | 9 | Kill (cannot be caught) |
| `SIGUSR1` | 10 | User-defined signal 1 |
| `SIGSEGV` | 11 | Segmentation violation |
| `SIGUSR2` | 12 | User-defined signal 2 |
| `SIGPIPE` | 13 | Broken pipe |
| `SIGALRM` | 14 | Alarm clock |
| `SIGTERM` | 15 | Termination |
| `SIGCHLD` | 17 | Child status changed |

### Windows Event Constants

| Constant | Value | Description |
|----------|-------|-------------|
| `PHP_WINDOWS_EVENT_CTRL_C` | 0 | Ctrl+C pressed |
| `PHP_WINDOWS_EVENT_CTRL_BREAK` | 1 | Ctrl+Break pressed |

### Wait Flags

| Constant | Value | Description |
|----------|-------|-------------|
| `WNOHANG` | 1 | Don't hang in wait |
| `WUNTRACED` | 2 | Tell about stopped, untraced children |

### Memory Thresholds

| Constant | Value | Description |
|----------|-------|-------------|
| `MEMORY_NORMAL_THRESHOLD` | 0.6 | 60% memory usage |
| `MEMORY_WARNING_THRESHOLD` | 0.75 | 75% memory usage |
| `MEMORY_CRITICAL_THRESHOLD` | 0.85 | 85% memory usage |
| `MEMORY_EMERGENCY_THRESHOLD` | 0.95 | 95% memory usage |

### File Permissions

| Constant | Value | Description |
|----------|-------|-------------|
| `FILE_PERM_READ_ONLY` | 0444 | Read-only for all |
| `FILE_PERM_READ_WRITE` | 0644 | Read/write for owner, read for others |
| `FILE_PERM_EXECUTABLE` | 0755 | Full for owner, read/execute for others |
| `FILE_PERM_FULL` | 0777 | Full permissions for all |

### Timeout Constants

| Constant | Value | Description |
|----------|-------|-------------|
| `TIMEOUT_SHORT` | 5 | 5 seconds |
| `TIMEOUT_MEDIUM` | 30 | 30 seconds |
| `TIMEOUT_LONG` | 300 | 5 minutes |
| `TIMEOUT_VERY_LONG` | 3600 | 1 hour |

### Buffer Sizes

| Constant | Value | Description |
|----------|-------|-------------|
| `BUFFER_SIZE_SMALL` | 1024 | 1KB |
| `BUFFER_SIZE_MEDIUM` | 8192 | 8KB |
| `BUFFER_SIZE_LARGE` | 65536 | 64KB |
| `BUFFER_SIZE_HUGE` | 1048576 | 1MB |

## Methods

### `getSafeSignal(string $signalName): ?int`

Returns a signal constant value with fallback. Returns `null` if the signal is not recognized.

```php
$sigint = Constants::getSafeSignal('SIGINT'); // Returns 2 or system-defined SIGINT
```

### `getSafeWindowsEvent(string $eventName): ?int`

Returns a Windows event constant value with fallback. Returns `null` if the event is not recognized.

```php
$ctrlc = Constants::getSafeWindowsEvent('PHP_WINDOWS_EVENT_CTRL_C'); // Returns 0 or system-defined value
```

### `getSignal(string $signalName): int`

Returns a signal constant value. Throws `InvalidArgumentException` if the signal is not recognized.

### `getWindowsEvent(string $eventName): int`

Returns a Windows event constant value. Throws `InvalidArgumentException` if the event is not recognized.

### `getWaitFlag(string $flagName): int`

Returns a wait flag constant value. Throws `InvalidArgumentException` if the flag is not recognized.

### Platform Detection Methods

- `isWindows(): bool` - Returns true if running on Windows
- `isUnix(): bool` - Returns true if running on Unix-like system
- `hasPcntl(): bool` - Returns true if PCNTL extension is loaded
- `hasPosix(): bool` - Returns true if POSIX extension is loaded

## Examples

### Cross-platform Signal Handler

```php
use venndev\vosaka\Constants;

class SignalHandler
{
    public function setup(): void
    {
        if (Constants::isWindows()) {
            $this->setupWindowsHandlers();
        } elseif (Constants::hasPcntl()) {
            $this->setupPosixHandlers();
        }
    }

    private function setupWindowsHandlers(): void
    {
        if (function_exists('sapi_windows_set_ctrl_handler')) {
            sapi_windows_set_ctrl_handler([$this, 'handleWindowsEvent']);
        }
    }

    private function setupPosixHandlers(): void
    {
        pcntl_async_signals(true);
        pcntl_signal(Constants::getSafeSignal('SIGINT'), [$this, 'handleSignal']);
        pcntl_signal(Constants::getSafeSignal('SIGTERM'), [$this, 'handleSignal']);
    }

    public function handleSignal(int $signal): void
    {
        echo "Received signal: $signal\n";
        exit(0);
    }

    public function handleWindowsEvent(int $event): void
    {
        $ctrlc = Constants::getSafeWindowsEvent('PHP_WINDOWS_EVENT_CTRL_C');
        if ($event === $ctrlc) {
            echo "Ctrl+C pressed\n";
            exit(0);
        }
    }
}
```

### Process Management

```php
use venndev\vosaka\Constants;

class ProcessManager
{
    public function terminateProcess($process): void
    {
        if (Constants::isWindows()) {
            proc_terminate($process);
        } else {
            // Try graceful termination first
            $sigterm = Constants::getSafeSignal('SIGTERM');
            proc_terminate($process, $sigterm);

            // Wait a bit
            sleep(2);

            // Force kill if still running
            $status = proc_get_status($process);
            if ($status['running']) {
                $sigkill = Constants::getSafeSignal('SIGKILL');
                proc_terminate($process, $sigkill);
            }
        }
    }
}
```

## Best Practices

1. **Always use safe methods** - Use `getSafeSignal()` and `getSafeWindowsEvent()` instead of direct constant access
2. **Check platform capabilities** - Use `isWindows()`, `hasPcntl()`, etc. before using platform-specific features
3. **Handle graceful degradation** - Provide fallbacks when certain features are not available
4. **Use predefined constants** - Leverage the predefined memory, timeout, and buffer size constants for consistency

## Contributing

When adding new constants:

1. Add the constant definition to the class
2. Add fallback logic in the appropriate getter method
3. Update this documentation
4. Add examples if needed
5. Test on multiple platforms

## License

This is part of the VOsaka project and follows the same license terms.
