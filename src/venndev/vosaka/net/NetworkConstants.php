<?php

declare(strict_types=1);

namespace venndev\vosaka\net;

/**
 * Network-related constants for buffer sizes, timeouts, and limits.
 */
final class NetworkConstants
{
    // === General ===
    /** Default socket timeout (seconds) */
    public const DEFAULT_TIMEOUT = 30;

    // === TCP settings ===
    /** Max bytes to read per cycle (2MB) */
    public const TCP_MAX_BYTES_PER_CYCLE = 2_097_152;
    /** Max read cycles per event loop tick */
    public const TCP_MAX_READ_CYCLES = 10;
    /** Max write cycles per event loop tick */
    public const TCP_MAX_WRITE_CYCLES = 5;
    /** TCP read buffer size (512KB) */
    public const TCP_READ_BUFFER_SIZE = 524_288;
    /** TCP write buffer size (512KB) */
    public const TCP_WRITE_BUFFER_SIZE = 524_288;

    // === UNIX socket settings ===
    /** UNIX socket read buffer size (1MB) */
    public const UNIX_READ_BUFFER_SIZE = 1_048_576;
}
