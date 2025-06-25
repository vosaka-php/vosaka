<?php

declare(strict_types=1);

namespace venndev\vosaka\sync;

use Generator;
use InvalidArgumentException;

final class Semaphore
{
    private int $count;
    private int $maxCount;

    public function __construct(int $maxCount)
    {
        if ($maxCount <= 0) {
            throw new InvalidArgumentException('Semaphore count must be greater than zero.');
        }
        $this->maxCount = $maxCount;
        $this->count = 0;
    }

    public function acquire(): Generator
    {
        while ($this->count >= $this->maxCount) {
            yield;
        }

        $this->count++;
    }

    public function release(): void
    {
        if ($this->count > 0) {
            $this->count--;
        }
    }

    public function getCount(): int
    {
        return $this->count;
    }
}