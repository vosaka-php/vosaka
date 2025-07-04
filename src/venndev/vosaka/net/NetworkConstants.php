<?php

declare(strict_types=1);

namespace venndev\vosaka\net;

final class NetworkConstants
{
    public const DEFAULT_TIMEOUT = 30;
    public const MAX_BYTES_PER_CYCLE = 2_097_152;
    public const MAX_READ_CYCLES = 10;
    public const MAX_WRITE_CYCLES = 200;
    public const READ_BUFFER_SIZE = 32768;
    public const WRITE_BUFFER_SIZE = 32768;
}
