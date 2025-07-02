<?php

declare(strict_types=1);

namespace venndev\vosaka\core\interfaces;

interface Time
{
    public static function new(float $seconds): self;

    public static function ms(int $milliseconds): self;

    public static function us(int $microseconds): self;
}
