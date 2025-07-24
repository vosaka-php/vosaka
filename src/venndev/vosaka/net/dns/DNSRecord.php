<?php

declare(strict_types=1);

namespace venndev\vosaka\net\dns;

/**
 * DNS Record representation
 */
class DNSRecord
{
    public function __construct(
        public readonly string $name,
        public readonly RecordType $type,
        public readonly QueryClass $class,
        public readonly int $ttl,
        public readonly mixed $data
    ) {}

    public function __toString(): string
    {
        return sprintf(
            "%s %d %s %s %s",
            $this->name,
            $this->ttl,
            $this->class->name,
            $this->type->name,
            $this->dataToString()
        );
    }

    private function dataToString(): string
    {
        return match ($this->type) {
            RecordType::A, RecordType::AAAA => $this->data,
            RecordType::MX => "{$this->data['priority']} {$this->data['exchange']}",
            RecordType::TXT => '"' . implode('" "', (array)$this->data) . '"',
            RecordType::SRV => sprintf(
                "%d %d %d %s",
                $this->data['priority'],
                $this->data['weight'],
                $this->data['port'],
                $this->data['target']
            ),
            default => json_encode($this->data)
        };
    }
}
