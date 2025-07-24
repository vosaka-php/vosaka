<?php

declare(strict_types=1);

namespace venndev\vosaka\net\dns;

/**
 * DNS Query builder
 */
class DNSQuery
{
    private string $domain;
    private RecordType $type = RecordType::A;
    private QueryClass $class = QueryClass::IN;
    private int $id;
    private bool $recursionDesired = true;

    public function __construct(string $domain)
    {
        $this->domain = $domain;
        $this->id = random_int(0, 65535);
    }

    public function setType(RecordType $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function setClass(QueryClass $class): self
    {
        $this->class = $class;
        return $this;
    }

    public function setRecursionDesired(bool $desired): self
    {
        $this->recursionDesired = $desired;
        return $this;
    }

    /**
     * Build DNS query packet
     */
    public function build(): string
    {
        $packet = '';

        // Header
        $flags = 0;
        if ($this->recursionDesired) {
            $flags |= 0x0100; // RD flag
        }

        // Logic at: https://datatracker.ietf.org/doc/html/rfc1035#section-4.1.1
        $packet .= pack('n', $this->id);        // Transaction ID
        $packet .= pack('n', $flags);            // Flags
        $packet .= pack('n', 1);                 // Questions
        $packet .= pack('n', 0);                 // Answers
        $packet .= pack('n', 0);                 // Authority
        $packet .= pack('n', 0);                 // Additional

        // Question
        $packet .= $this->encodeDomain($this->domain);
        $packet .= pack('n', $this->type->value);
        $packet .= pack('n', $this->class->value);

        return $packet;
    }

    /**
     * Encode domain name
     */
    private function encodeDomain(string $domain): string
    {
        $encoded = '';
        $labels = explode('.', $domain);

        foreach ($labels as $label) {
            $encoded .= chr(strlen($label)) . $label;
        }

        $encoded .= "\0"; // Root label

        return $encoded;
    }

    public function getId(): int
    {
        return $this->id;
    }
}
