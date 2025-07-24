<?php

declare(strict_types=1);

namespace venndev\vosaka\net\contracts;

use venndev\vosaka\core\Result;

/**
 * Extended interface for stream-based connections
 */
interface StreamInterface extends ConnectionInterface
{
    /**
     * Read a line (until \n)
     * 
     * @return Result<string>
     */
    public function readLine(): Result;

    /**
     * Read until delimiter is found
     * 
     * @param string $delimiter
     * @return Result<string>
     */
    public function readUntil(string $delimiter): Result;

    /**
     * Read exact number of bytes
     * 
     * @param int $bytes
     * @return Result<string>
     */
    public function readExact(int $bytes): Result;

    /**
     * Write all data, retrying if necessary
     * 
     * @param string $data
     * @return Result<void>
     */
    public function writeAll(string $data): Result;

    /**
     * Flush write buffer
     * 
     * @return Result<void>
     */
    public function flush(): Result;

    /**
     * Check if data is available for reading
     */
    public function readable(): bool;

    /**
     * Check if connection is ready for writing
     */
    public function writable(): bool;
}
