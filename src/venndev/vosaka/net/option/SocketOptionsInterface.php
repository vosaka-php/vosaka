<?php

declare(strict_types=1);

namespace venndev\vosaka\net\option;

/**
 * Interface cho Socket Options
 */
interface SocketOptionsInterface
{
    public function toArray(): array;
    public function merge(SocketOptionsInterface $other): self;
}
