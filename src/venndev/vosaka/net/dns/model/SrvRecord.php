<?php

declare(strict_types=1);

namespace venndev\vosaka\net\dns\model;

final class SrvRecord
{
    public function __construct(
        public bool $success,
        public ?string $error,
        public int $priority,
        public int $weight,
        public int $port,
        public string $target
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
     * Get the SRV priority
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * Get the SRV weight
     */
    public function getWeight(): int
    {
        return $this->weight;
    }

    /**
     * Get the SRV port
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * Get the SRV target hostname
     */
    public function getTarget(): string
    {
        return $this->target;
    }

    /**
     * Check if this is a higher priority than another SRV record
     * (lower priority value means higher priority)
     */
    public function hasHigherPriorityThan(SrvRecord $other): bool
    {
        return $this->priority < $other->priority;
    }

    /**
     * Check if this is a lower priority than another SRV record
     * (higher priority value means lower priority)
     */
    public function hasLowerPriorityThan(SrvRecord $other): bool
    {
        return $this->priority > $other->priority;
    }

    /**
     * Check if this has the same priority as another SRV record
     */
    public function hasSamePriorityAs(SrvRecord $other): bool
    {
        return $this->priority === $other->priority;
    }

    /**
     * Get the service endpoint as host:port
     */
    public function getEndpoint(): string
    {
        return $this->target . ':' . $this->port;
    }

    /**
     * Check if the target is a null target (.)
     */
    public function isNullTarget(): bool
    {
        return $this->target === '.' || $this->target === '';
    }

    /**
     * Convert to array format for backwards compatibility
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'error' => $this->error,
            'priority' => $this->priority,
            'weight' => $this->weight,
            'port' => $this->port,
            'target' => $this->target,
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
            $data['priority'] ?? 0,
            $data['weight'] ?? 0,
            $data['port'] ?? 0,
            $data['target'] ?? ''
        );
    }

    /**
     * String representation
     */
    public function __toString(): string
    {
        if (!$this->success) {
            return "SRV Record Error: " . ($this->error ?? 'Unknown error');
        }

        return "SRV {$this->priority} {$this->weight} {$this->port} {$this->target}";
    }

    /**
     * Compare two SRV records for sorting
     * Returns negative if this record should come first, positive if second, 0 if equal
     */
    public function compare(SrvRecord $other): int
    {
        // First sort by priority (lower is better)
        if ($this->priority !== $other->priority) {
            return $this->priority - $other->priority;
        }

        // If priorities are equal, sort by weight (higher is better for weighted selection)
        return $other->weight - $this->weight;
    }
}
