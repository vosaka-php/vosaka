<?php

declare(strict_types=1);

namespace venndev\vosaka\sync;

use Generator;
use venndev\vosaka\utils\sync\CancelFuture;

final class CancelToken
{
    private bool $isCancelled = false;
    private mixed $cancelledValue = null;
    private static int $nextId = 0;
    private int $id;

    /**
     * Summary of tokens
     * @var array<int, CancelToken>
     */
    private static array $tokens = [];

    public function __construct()
    {
        $this->id = self::$nextId++;
        self::$tokens[$this->id] = $this;
    }

    public function cancel(): void
    {
        $this->isCancelled = true;
        $this->save();
    }

    public function cancelWithValue(mixed $value): void
    {
        $this->isCancelled = true;
        $this->cancelledValue = $value;
        $this->save();
    }

    public function cancelFurture(): Generator
    {
        return yield new CancelFuture();
    }

    public function isCancelled(): bool
    {
        return self::$tokens[$this->id]->isCancelled;
    }

    public function close(): void
    {
        if (isset(self::$tokens[$this->id])) {
            unset(self::$tokens[$this->id]);
        }
    }

    private function save(): void
    {
        self::$tokens[$this->id] = $this;
    }
}