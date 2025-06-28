<?php

declare(strict_types=1);

namespace venndev\vosaka\net\dns\model;

final class NameRecord
{
    public function __construct(
        public bool $success,
        public ?string $error,
        public string $name
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
     * Get the name value
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Check if the name is a fully qualified domain name (FQDN)
     */
    public function isFQDN(): bool
    {
        return str_ends_with($this->name, '.');
    }

    /**
     * Get the name without trailing dot if present
     */
    public function getNameWithoutDot(): string
    {
        return rtrim($this->name, '.');
    }

    /**
     * Get the name with trailing dot (FQDN format)
     */
    public function getFQDN(): string
    {
        return $this->isFQDN() ? $this->name : $this->name . '.';
    }

    /**
     * Get the domain parts as array
     */
    public function getDomainParts(): array
    {
        $name = $this->getNameWithoutDot();
        return $name === '' ? [] : explode('.', $name);
    }

    /**
     * Get the top-level domain
     */
    public function getTLD(): string
    {
        $parts = $this->getDomainParts();
        return empty($parts) ? '' : end($parts);
    }

    /**
     * Get the subdomain parts (everything except TLD)
     */
    public function getSubdomains(): array
    {
        $parts = $this->getDomainParts();
        return empty($parts) ? [] : array_slice($parts, 0, -1);
    }

    /**
     * Check if this is a subdomain of another domain
     */
    public function isSubdomainOf(string $domain): bool
    {
        $cleanName = $this->getNameWithoutDot();
        $cleanDomain = rtrim($domain, '.');

        return str_ends_with($cleanName, '.' . $cleanDomain) || $cleanName === $cleanDomain;
    }

    /**
     * Get the number of domain levels
     */
    public function getDomainLevels(): int
    {
        return count($this->getDomainParts());
    }

    /**
     * Check if the name represents a root domain
     */
    public function isRoot(): bool
    {
        return $this->name === '.' || $this->name === '';
    }

    /**
     * Convert to array format for backwards compatibility
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'error' => $this->error,
            'name' => $this->name,
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
            $data['name'] ?? ''
        );
    }

    /**
     * String representation
     */
    public function __toString(): string
    {
        if (!$this->success) {
            return "Name Record Error: " . ($this->error ?? 'Unknown error');
        }

        return $this->name;
    }

    /**
     * Compare names (case-insensitive)
     */
    public function equals(string $otherName): bool
    {
        return strcasecmp($this->getNameWithoutDot(), rtrim($otherName, '.')) === 0;
    }

    /**
     * Check if name matches a pattern (supports wildcards)
     */
    public function matches(string $pattern): bool
    {
        $name = $this->getNameWithoutDot();
        $pattern = rtrim($pattern, '.');

        // Convert DNS wildcard pattern to regex
        $regex = '/^' . str_replace(['*', '.'], ['[^.]*', '\.'], $pattern) . '$/i';

        return preg_match($regex, $name) === 1;
    }
}
