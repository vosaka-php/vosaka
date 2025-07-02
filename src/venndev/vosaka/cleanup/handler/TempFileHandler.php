<?php

declare(strict_types=1);

namespace venndev\vosaka\cleanup\handler;

use venndev\vosaka\cleanup\logger\LoggerInterface;

/**
 * Handles temporary file cleanup
 */
final class TempFileHandler
{
    private array $tempFiles = [];
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function addTempFile(string $filePath): self
    {
        if (file_exists($filePath)) {
            $this->tempFiles[$filePath] = $filePath;
            $this->logger->log("Added temp file: $filePath");
        }
        return $this;
    }

    public function removeTempFile(string $path): void
    {
        if (isset($this->tempFiles[$path])) {
            unset($this->tempFiles[$path]);
            $this->logger->log("Removed temp file from array: $path");
        }
    }

    public function cleanupAll(): void
    {
        foreach ($this->tempFiles as $file) {
            if (file_exists($file)) {
                @unlink($file);
                $this->logger->log("Deleted temp file: $file");
            }
        }
        $this->tempFiles = [];
    }

    public function getTempFiles(): array
    {
        return $this->tempFiles;
    }

    public function getCount(): int
    {
        return count($this->tempFiles);
    }
}