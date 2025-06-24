<?php

declare(strict_types=1);

namespace venndev\vosaka\time;

final class Interval implements \venndev\vosaka\core\interfaces\Time
{
    public function __construct(public float $seconds)
    {
        // TODO: Implement the logic for handling sleep instructions.
    }

    public static function c(float $seconds): self
    {
        return new self($seconds);
    }

    public static function ms(int $milliseconds): self
    {
        return new self($milliseconds / 1000.0); // Convert milliseconds to seconds
    }

    public static function us(int $microseconds): self
    {
        return new self($microseconds / 1_000_000.0); // Convert microseconds to seconds
    }

    public static function tick(): self
    {
        return new self(0.001); // 1 millisecond
    }
}