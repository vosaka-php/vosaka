<?php

declare(strict_types=1);

namespace venndev\vosaka\net\dns\model;

final class MxRecord
{
    public function __construct(
        public bool $success,
        public ?string $error,
        public int $preference,
        public string $exchange
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
     * Get the MX preference (priority)
     */
    public function getPreference(): int
    {
        return $this->preference;
    }

    /**
     * Get the MX exchange (mail server hostname)
     */
    public function getExchange(): string
    {
        return $this->exchange;
    }

    /**
     * Get priority (alias for preference)
     */
    public function getPriority(): int
    {
        return $this->preference;
    }

    /**
     * Get mail server hostname (alias for exchange)
     */
    public function getMailServer(): string
    {
        return $this->exchange;
    }

    /**
     * Convert to array format for backwards compatibility
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'error' => $this->error,
            'preference' => $this->preference,
            'exchange' => $this->exchange,
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
            $data['preference'] ?? 0,
            $data['exchange'] ?? ''
        );
    }

    /**
     * String representation showing preference and exchange
     */
    public function __toString(): string
    {
        if (!$this->success) {
            return "MX Record Error: " . ($this->error ?? 'Unknown error');
        }

        return "MX {$this->preference} {$this->exchange}";
    }
}
