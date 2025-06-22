<?php

declare(strict_types=1);

namespace venndev\vosaka\time;

final class Sleep
{
    public function __construct(public float $seconds)
    {
        // TODO: Implement the logic for handling sleep instructions.
    }

    public static function c(float $seconds): self
    {
        return new self($seconds);
    }
}