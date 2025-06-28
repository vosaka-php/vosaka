<?php

declare(strict_types=1);

namespace venndev\vosaka\net\dns\model;

final class RawRecord
{
    public function __construct(
        public bool $success,
        public ?string $error,
        public string $raw
    ) {}

    /**
     * Check if the record parsing was successful
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * Get error message if parsing failed
     */
    public function getError(): ?string
    {
        return $this->error;
    }

    /**
     * Get the raw hexadecimal data
     */
    public function getRaw(): string
    {
        return $this->raw;
    }

    /**
     * Get the raw data as binary
     */
    public function getBinary(): string
    {
        return hex2bin($this->raw) ?: '';
    }

    /**
     * Get the length of the raw data in bytes
     */
    public function getLength(): int
    {
        return strlen($this->raw) / 2;
    }

    /**
     * Get the raw data as an array of bytes
     */
    public function getBytes(): array
    {
        $binary = $this->getBinary();
        $bytes = [];
        for ($i = 0; $i < strlen($binary); $i++) {
            $bytes[] = ord($binary[$i]);
        }
        return $bytes;
    }

    /**
     * Get formatted hex dump of the raw data
     */
    public function getHexDump(): string
    {
        $hex = $this->raw;
        $formatted = '';

        for ($i = 0; $i < strlen($hex); $i += 32) {
            $line = substr($hex, $i, 32);
            $formatted .= chunk_split($line, 2, ' ');
            $formatted .= "\n";
        }

        return trim($formatted);
    }

    /**
     * Check if the raw data is empty
     */
    public function isEmpty(): bool
    {
        return empty($this->raw);
    }

    /**
     * Search for a pattern in the raw hex data
     */
    public function contains(string $hexPattern): bool
    {
        return str_contains(strtolower($this->raw), strtolower($hexPattern));
    }

    /**
     * Extract a portion of the raw data
     */
    public function extract(int $start, int $length = null): string
    {
        $startHex = $start * 2;
        $lengthHex = $length !== null ? $length * 2 : null;

        return substr($this->raw, $startHex, $lengthHex);
    }

    /**
     * Try to interpret raw data as printable text
     */
    public function getAsText(): string
    {
        $binary = $this->getBinary();
        $text = '';

        for ($i = 0; $i < strlen($binary); $i++) {
            $char = $binary[$i];
            if (ctype_print($char) || $char === ' ') {
                $text .= $char;
            } else {
                $text .= '.';
            }
        }

        return $text;
    }

    /**
     * Convert to array format for backwards compatibility
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'error' => $this->error,
            'raw' => $this->raw,
        ];
    }

    /**
     * Create from array data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['success'] ?? false,
            $data['error'] ?? null,
            $data['raw'] ?? ''
        );
    }

    /**
     * Create from binary data
     */
    public static function fromBinary(string $binary, bool $success = true, ?string $error = null): self
    {
        return new self(
            $success,
            $error,
            bin2hex($binary)
        );
    }

    /**
     * String representation
     */
    public function __toString(): string
    {
        if (!$this->success) {
            return "Raw Record Error: " . ($this->error ?? 'Unknown error');
        }

        return "Raw data ({$this->getLength()} bytes): " . strtoupper($this->raw);
    }

    /**
     * Debug representation with hex dump
     */
    public function toDebugString(): string
    {
        if (!$this->success) {
            return $this->__toString();
        }

        $result = "Raw Record ({$this->getLength()} bytes):\n";
        $result .= "Hex: " . strtoupper($this->raw) . "\n";
        $result .= "Text: " . $this->getAsText() . "\n";
        $result .= "Dump:\n" . $this->getHexDump();

        return $result;
    }
}
