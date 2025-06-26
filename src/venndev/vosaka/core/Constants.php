<?php

declare(strict_types=1);

namespace venndev\vosaka\core;

use InvalidArgumentException;

final class Constants
{
    // POSIX Signal constants
    public const SIGHUP = 1; // Hangup
    public const SIGINT = 2; // Interrupt (Ctrl+C)
    public const SIGQUIT = 3; // Quit
    public const SIGILL = 4; // Illegal instruction
    public const SIGTRAP = 5; // Trap
    public const SIGABRT = 6; // Abort
    public const SIGBUS = 7; // Bus error
    public const SIGFPE = 8; // Floating point exception
    public const SIGKILL = 9; // Kill (cannot be caught or ignored)
    public const SIGUSR1 = 10; // User-defined signal 1
    public const SIGSEGV = 11; // Segmentation violation
    public const SIGUSR2 = 12; // User-defined signal 2
    public const SIGPIPE = 13; // Broken pipe
    public const SIGALRM = 14; // Alarm clock
    public const SIGTERM = 15; // Termination
    public const SIGCHLD = 17; // Child status changed
    public const SIGCONT = 18; // Continue
    public const SIGSTOP = 19; // Stop (cannot be caught or ignored)
    public const SIGTSTP = 20; // Keyboard stop
    public const SIGTTIN = 21; // Background read from tty
    public const SIGTTOU = 22; // Background write to tty

    // Windows event constants
    public const PHP_WINDOWS_EVENT_CTRL_C = 0;
    public const PHP_WINDOWS_EVENT_CTRL_BREAK = 1;

    // Process control wait flags
    public const WNOHANG = 1; // Don't hang in wait
    public const WUNTRACED = 2; // Tell about stopped, untraced children

    // File mode constants
    public const S_IRUSR = 0400; // Read permission for owner
    public const S_IWUSR = 0200; // Write permission for owner
    public const S_IXUSR = 0100; // Execute permission for owner
    public const S_IRGRP = 0040; // Read permission for group
    public const S_IWGRP = 0020; // Write permission for group
    public const S_IXGRP = 0010; // Execute permission for group
    public const S_IROTH = 0004; // Read permission for others
    public const S_IWOTH = 0002; // Write permission for others
    public const S_IXOTH = 0001; // Execute permission for others

    // Common file permissions
    public const FILE_PERM_READ_ONLY = 0444;
    public const FILE_PERM_READ_WRITE = 0644;
    public const FILE_PERM_EXECUTABLE = 0755;
    public const FILE_PERM_FULL = 0777;

    // Error levels for fatal error detection
    public const FATAL_ERROR_TYPES = [
        E_ERROR,
        E_PARSE,
        E_CORE_ERROR,
        E_COMPILE_ERROR,
        E_USER_ERROR,
    ];

    // Memory thresholds (in percentage)
    public const MEMORY_NORMAL_THRESHOLD = 0.6; // 60%
    public const MEMORY_WARNING_THRESHOLD = 0.75; // 75%
    public const MEMORY_CRITICAL_THRESHOLD = 0.85; // 85%
    public const MEMORY_EMERGENCY_THRESHOLD = 0.95; // 95%

    // Process priorities (Unix nice values)
    public const PRIORITY_HIGH = -10;
    public const PRIORITY_NORMAL = 0;
    public const PRIORITY_LOW = 10;
    public const PRIORITY_IDLE = 19;

    // Timeout constants (in seconds)
    public const TIMEOUT_SHORT = 5;
    public const TIMEOUT_MEDIUM = 30;
    public const TIMEOUT_LONG = 300; // 5 minutes
    public const TIMEOUT_VERY_LONG = 3600; // 1 hour

    // Buffer sizes
    public const BUFFER_SIZE_SMALL = 1024; // 1KB
    public const BUFFER_SIZE_MEDIUM = 8192; // 8KB
    public const BUFFER_SIZE_LARGE = 65536; // 64KB
    public const BUFFER_SIZE_HUGE = 1048576; // 1MB

    // Network constants
    public const DEFAULT_TCP_PORT = 8080;
    public const DEFAULT_UDP_PORT = 8081;
    public const MAX_CONNECTIONS = 1000;
    public const SOCKET_TIMEOUT = 30;

    /**
     * Get a signal constant value with fallback
     */
    public static function getSignal(string $signalName): int
    {
        $signalName = strtoupper($signalName);

        // First try to get the system-defined constant
        if (defined($signalName)) {
            return constant($signalName);
        }

        // Fallback to our constants
        return match ($signalName) {
            "SIGHUP" => self::SIGHUP,
            "SIGINT" => self::SIGINT,
            "SIGQUIT" => self::SIGQUIT,
            "SIGILL" => self::SIGILL,
            "SIGTRAP" => self::SIGTRAP,
            "SIGABRT" => self::SIGABRT,
            "SIGBUS" => self::SIGBUS,
            "SIGFPE" => self::SIGFPE,
            "SIGKILL" => self::SIGKILL,
            "SIGUSR1" => self::SIGUSR1,
            "SIGSEGV" => self::SIGSEGV,
            "SIGUSR2" => self::SIGUSR2,
            "SIGPIPE" => self::SIGPIPE,
            "SIGALRM" => self::SIGALRM,
            "SIGTERM" => self::SIGTERM,
            "SIGCHLD" => self::SIGCHLD,
            "SIGCONT" => self::SIGCONT,
            "SIGSTOP" => self::SIGSTOP,
            "SIGTSTP" => self::SIGTSTP,
            "SIGTTIN" => self::SIGTTIN,
            "SIGTTOU" => self::SIGTTOU,
            default => throw new InvalidArgumentException(
                "Unknown signal: $signalName"
            ),
        };
    }

    /**
     * Get a Windows event constant with fallback
     */
    public static function getWindowsEvent(string $eventName): int
    {
        $eventName = strtoupper($eventName);

        // First try to get the system-defined constant
        if (defined($eventName)) {
            return constant($eventName);
        }

        // Fallback to our constants
        return match ($eventName) {
            "PHP_WINDOWS_EVENT_CTRL_C" => self::PHP_WINDOWS_EVENT_CTRL_C,
            "PHP_WINDOWS_EVENT_CTRL_BREAK"
                => self::PHP_WINDOWS_EVENT_CTRL_BREAK,
            default => throw new InvalidArgumentException(
                "Unknown Windows event: $eventName"
            ),
        };
    }

    /**
     * Check if we're running on Windows
     */
    public static function isWindows(): bool
    {
        return strtoupper(substr(PHP_OS, 0, 3)) === "WIN";
    }

    /**
     * Check if we're running on Unix-like system
     */
    public static function isUnix(): bool
    {
        return !self::isWindows();
    }

    /**
     * Check if PCNTL extension is available
     */
    public static function hasPcntl(): bool
    {
        return extension_loaded("pcntl");
    }

    /**
     * Check if POSIX extension is available
     */
    public static function hasPosix(): bool
    {
        return extension_loaded("posix");
    }

    /**
     * Get the appropriate wait flag with fallback
     */
    public static function getWaitFlag(string $flagName): int
    {
        $flagName = strtoupper($flagName);

        // First try to get the system-defined constant
        if (defined($flagName)) {
            return constant($flagName);
        }

        // Fallback to our constants
        return match ($flagName) {
            "WNOHANG" => self::WNOHANG,
            "WUNTRACED" => self::WUNTRACED,
            default => throw new InvalidArgumentException(
                "Unknown wait flag: $flagName"
            ),
        };
    }

    /**
     * Get safe signal value for the current platform
     */
    public static function getSafeSignal(string $signalName): ?int
    {
        try {
            return self::getSignal($signalName);
        } catch (InvalidArgumentException) {
            return null;
        }
    }

    /**
     * Get safe Windows event value for the current platform
     */
    public static function getSafeWindowsEvent(string $eventName): ?int
    {
        try {
            return self::getWindowsEvent($eventName);
        } catch (InvalidArgumentException) {
            return null;
        }
    }
}
