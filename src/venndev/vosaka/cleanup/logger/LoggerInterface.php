<?php

declare(strict_types=1);

namespace venndev\vosaka\cleanup\logger;

/**
 * Logger interface
 */
interface LoggerInterface
{
    public function log(string $message): void;
    public function setLogging(bool $enableLogging): void;
}