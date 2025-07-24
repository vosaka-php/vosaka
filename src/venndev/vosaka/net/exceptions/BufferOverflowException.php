<?php

declare(strict_types=1);

namespace venndev\vosaka\net\exceptions;

/**
 * Exception thrown when buffer overflow occurs
 */
class BufferOverflowException extends NetworkException
{
    private int $bufferSize;
    private int $dataSize;

    public function __construct(int $bufferSize, int $dataSize)
    {
        $this->bufferSize = $bufferSize;
        $this->dataSize = $dataSize;

        parent::__construct(
            "Buffer overflow: attempted to write {$dataSize} bytes to buffer of size {$bufferSize}"
        );
    }

    public function getBufferSize(): int
    {
        return $this->bufferSize;
    }

    public function getDataSize(): int
    {
        return $this->dataSize;
    }
}
