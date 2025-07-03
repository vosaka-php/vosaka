<?php

declare(strict_types=1);

namespace venndev\vosaka\net;

final class NetworkConstants
{
    public const DEFAULT_TIMEOUT = 30;

    public const TCP_MAX_BYTES_PER_CYCLE = 2_097_152;
    public const TCP_MAX_READ_CYCLES = 10;
    public const TCP_MAX_WRITE_CYCLES = 5;
    public const TCP_READ_BUFFER_SIZE = 524_288;
    public const TCP_WRITE_BUFFER_SIZE = 524_288;

    public const UNIX_READ_BUFFER_SIZE = 1_048_576;
}
