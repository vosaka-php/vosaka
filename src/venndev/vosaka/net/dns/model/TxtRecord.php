<?php

declare(strict_types=1);

namespace venndev\vosaka\net\dns\model;

final class TxtRecord
{
    public function __construct(
        public bool $success,
        public ?string $error,
        public string $text,
        public array $segments
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
     * Get the complete TXT record content
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * Get the individual TXT record segments
     */
    public function getSegments(): array
    {
        return $this->segments;
    }

    /**
     * Get the number of TXT segments
     */
    public function getSegmentCount(): int
    {
        return count($this->segments);
    }

    /**
     * Check if TXT record contains specific text
     */
    public function contains(string $needle): bool
    {
        return str_contains($this->text, $needle);
    }

    /**
     * Check if TXT record starts with specific text
     */
    public function startsWith(string $prefix): bool
    {
        return str_starts_with($this->text, $prefix);
    }

    /**
     * Get TXT record length
     */
    public function getLength(): int
    {
        return strlen($this->text);
    }

    /**
     * Split TXT record by delimiter
     */
    public function split(string $delimiter = ' '): array
    {
        return explode($delimiter, $this->text);
    }

    /**
     * Convert to array format for backwards compatibility
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'error' => $this->error,
            'text' => $this->text,
            'segments' => $this->segments,
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
            $data['text'] ?? '',
            $data['segments'] ?? []
        );
    }

    /**
     * String representation
     */
    public function __toString(): string
    {
        if (!$this->success) {
            return "TXT Record Error: " . ($this->error ?? 'Unknown error');
        }

        return $this->text;
    }
}
