<?php

declare(strict_types=1);

namespace venndev\vosaka\cleanup\logger;

/**
 * Simple file logger implementation
 */
final class FileLogger implements LoggerInterface
{
    private bool $enableLogging;
    private string $logFile;

    public function __construct(string $logFile = '/tmp/graceful_shutdown.log', bool $enableLogging = false)
    {
        $this->logFile = $logFile;
        $this->enableLogging = $enableLogging;
    }

    public function log(string $message): void
    {
        if ($this->enableLogging) {
            $timestamp = date('Y-m-d H:i:s');
            @file_put_contents(
                $this->logFile,
                "[$timestamp] $message\n",
                FILE_APPEND
            );
        }
    }

    public function setLogging(bool $enableLogging): void
    {
        $this->enableLogging = $enableLogging;
        $this->log('Logging '.($enableLogging ? 'enabled' : 'disabled'));
    }

    public function setLogFile(string $logFile): void
    {
        $this->logFile = $logFile;
    }
}