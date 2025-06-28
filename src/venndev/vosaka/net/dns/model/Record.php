<?php

declare(strict_types=1);

namespace venndev\vosaka\net\dns\model;

final class Record
{
    public string $section = "";

    public function __construct(
        public string $name,
        public string $type,
        public int $class,
        public int $ttl,
        public mixed $data,
        public int $rawType,
        public int $nextOffset
    ) {}
}
