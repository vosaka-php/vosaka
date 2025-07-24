<?php

declare(strict_types=1);

namespace venndev\vosaka\net\dns;

/**
 * DNS Response parser
 */
class DNSResponse
{
    private string $data;
    private int $offset = 0;
    private int $id;
    private int $flags;
    private ResponseCode $responseCode;
    private array $questions = [];
    private array $answers = [];
    private array $authority = [];
    private array $additional = [];

    public function __construct(string $data)
    {
        $this->data = $data;
        $this->parse();
    }

    /**
     * Parse DNS response
     *
     * Link: https://datatracker.ietf.org/doc/html/rfc1035#section-4.1.1
     */
    private function parse(): void
    {
        // Header
        $this->id = unpack('n', substr($this->data, 0, 2))[1];
        $this->flags = unpack('n', substr($this->data, 2, 2))[1];
        $this->responseCode = ResponseCode::from($this->flags & 0x000F);

        $qdcount = unpack('n', substr($this->data, 4, 2))[1];
        $ancount = unpack('n', substr($this->data, 6, 2))[1];
        $nscount = unpack('n', substr($this->data, 8, 2))[1];
        $arcount = unpack('n', substr($this->data, 10, 2))[1];

        $this->offset = 12;

        // Questions
        for ($i = 0; $i < $qdcount; $i++) {
            $this->questions[] = $this->parseQuestion();
        }

        // Answers
        for ($i = 0; $i < $ancount; $i++) {
            $this->answers[] = $this->parseRecord();
        }

        // Authority
        for ($i = 0; $i < $nscount; $i++) {
            $this->authority[] = $this->parseRecord();
        }

        // Additional
        for ($i = 0; $i < $arcount; $i++) {
            $this->additional[] = $this->parseRecord();
        }
    }

    /**
     * Parse question section
     */
    private function parseQuestion(): array
    {
        $name = $this->parseDomain();
        $type = unpack('n', substr($this->data, $this->offset, 2))[1];
        $class = unpack('n', substr($this->data, $this->offset + 2, 2))[1];
        $this->offset += 4;

        return [
            'name' => $name,
            'type' => RecordType::from($type),
            'class' => QueryClass::from($class)
        ];
    }

    /**
     * Parse resource record
     */
    private function parseRecord(): DNSRecord
    {
        $name = $this->parseDomain();
        $type = unpack('n', substr($this->data, $this->offset, 2))[1];
        $class = unpack('n', substr($this->data, $this->offset + 2, 2))[1];
        $ttl = unpack('N', substr($this->data, $this->offset + 4, 4))[1];
        $rdlength = unpack('n', substr($this->data, $this->offset + 8, 2))[1];
        $this->offset += 10;

        $recordType = RecordType::from($type);
        $data = $this->parseRData($recordType, $rdlength);

        return new DNSRecord(
            $name,
            $recordType,
            QueryClass::from($class),
            $ttl,
            $data
        );
    }

    /**
     * Parse domain name with compression
     */
    private function parseDomain(): string
    {
        $labels = [];
        $jumped = false;
        $jumpOffset = 0;

        while (true) {
            if ($this->offset >= strlen($this->data)) {
                break;
            }

            $len = ord($this->data[$this->offset]);

            if ($len === 0) {
                $this->offset++;
                break;
            }

            // Check for compression
            if (($len & 0xC0) === 0xC0) {
                if (!$jumped) {
                    $jumpOffset = $this->offset + 2;
                }
                $pointer = (($len & 0x3F) << 8) | ord($this->data[$this->offset + 1]);
                $this->offset = $pointer;
                $jumped = true;
                continue;
            }

            $this->offset++;
            $labels[] = substr($this->data, $this->offset, $len);
            $this->offset += $len;
        }

        if ($jumped) {
            $this->offset = $jumpOffset;
        }

        return implode('.', $labels);
    }

    /**
     * Parse record data based on type
     */
    private function parseRData(RecordType $type, int $length): mixed
    {
        $data = substr($this->data, $this->offset, $length);
        $this->offset += $length;

        return match ($type) {
            RecordType::A => inet_ntop($data),
            RecordType::AAAA => inet_ntop($data),
            RecordType::NS, RecordType::CNAME, RecordType::PTR => $this->parseDomainInRData($data),
            RecordType::MX => $this->parseMX($data),
            RecordType::TXT => $this->parseTXT($data),
            RecordType::SRV => $this->parseSRV($data),
            RecordType::SOA => $this->parseSOA($data),
            default => $data
        };
    }

    /**
     * Parse domain in RDATA
     */
    private function parseDomainInRData(string $data): string
    {
        $oldOffset = $this->offset;
        $this->offset -= strlen($data);
        $domain = $this->parseDomain();
        $this->offset = $oldOffset;
        return $domain;
    }

    /**
     * Parse MX record
     */
    private function parseMX(string $data): array
    {
        $priority = unpack('n', substr($data, 0, 2))[1];
        $oldOffset = $this->offset;
        $this->offset -= strlen($data) - 2;
        $exchange = $this->parseDomain();
        $this->offset = $oldOffset;

        return [
            'priority' => $priority,
            'exchange' => $exchange
        ];
    }

    /**
     * Parse TXT record
     */
    private function parseTXT(string $data): array
    {
        $texts = [];
        $offset = 0;

        while ($offset < strlen($data)) {
            $len = ord($data[$offset]);
            $offset++;
            $texts[] = substr($data, $offset, $len);
            $offset += $len;
        }

        return $texts;
    }

    /**
     * Parse SRV record
     */
    private function parseSRV(string $data): array
    {
        $priority = unpack('n', substr($data, 0, 2))[1];
        $weight = unpack('n', substr($data, 2, 2))[1];
        $port = unpack('n', substr($data, 4, 2))[1];

        $oldOffset = $this->offset;
        $this->offset -= strlen($data) - 6;
        $target = $this->parseDomain();
        $this->offset = $oldOffset;

        return [
            'priority' => $priority,
            'weight' => $weight,
            'port' => $port,
            'target' => $target
        ];
    }

    /**
     * Parse SOA record
     */
    private function parseSOA(string $data): array
    {
        $oldOffset = $this->offset;
        $this->offset -= strlen($data);

        $mname = $this->parseDomain();
        $rname = $this->parseDomain();

        $remaining = substr($this->data, $this->offset, 20);
        $this->offset = $oldOffset;

        return [
            'mname' => $mname,
            'rname' => $rname,
            'serial' => unpack('N', substr($remaining, 0, 4))[1],
            'refresh' => unpack('N', substr($remaining, 4, 4))[1],
            'retry' => unpack('N', substr($remaining, 8, 4))[1],
            'expire' => unpack('N', substr($remaining, 12, 4))[1],
            'minimum' => unpack('N', substr($remaining, 16, 4))[1]
        ];
    }

    // Getters
    public function getId(): int
    {
        return $this->id;
    }
    public function getResponseCode(): ResponseCode
    {
        return $this->responseCode;
    }
    public function getAnswers(): array
    {
        return $this->answers;
    }
    public function getAuthority(): array
    {
        return $this->authority;
    }
    public function getAdditional(): array
    {
        return $this->additional;
    }

    public function isAuthoritative(): bool
    {
        return (bool)($this->flags & 0x0400);
    }

    public function isTruncated(): bool
    {
        return (bool)($this->flags & 0x0200);
    }
}
