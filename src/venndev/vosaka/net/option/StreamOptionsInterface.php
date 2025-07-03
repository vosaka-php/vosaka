<?php

declare(strict_types=1);

namespace venndev\vosaka\net\option;

/**
 * Interface cho Stream Options
 */
interface StreamOptionsInterface
{
    public function toArray(): array;
    public function merge(StreamOptionsInterface $other): self;
}
