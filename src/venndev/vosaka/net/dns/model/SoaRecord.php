<?php

declare(strict_types=1);

namespace venndev\vosaka\net\dns\model;

final class SoaRecord
{
    public function __construct(
        public bool $success,
        public ?string $error,
        public string $primary,
        public string $admin,
        public int $serial,
        public int $refresh,
        public int $retry,
        public int $expire,
        public int $minimum
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
     * Get the primary name server
     */
    public function getPrimary(): string
    {
        return $this->primary;
    }

    /**
     * Get the administrator email address
     */
    public function getAdmin(): string
    {
        return $this->admin;
    }

    /**
     * Get the serial number
     */
    public function getSerial(): int
    {
        return $this->serial;
    }

    /**
     * Get the refresh interval in seconds
     */
    public function getRefresh(): int
    {
        return $this->refresh;
    }

    /**
     * Get the retry interval in seconds
     */
    public function getRetry(): int
    {
        return $this->retry;
    }

    /**
     * Get the expire time in seconds
     */
    public function getExpire(): int
    {
        return $this->expire;
    }

    /**
     * Get the minimum TTL in seconds
     */
    public function getMinimum(): int
    {
        return $this->minimum;
    }

    /**
     * Get the administrator email in standard format
     */
    public function getAdminEmail(): string
    {
        // Convert DNS admin format (user.domain.com) to email format (user@domain.com)
        return str_replace(".", "@", $this->admin, 1);
    }

    /**
     * Get refresh interval in human readable format
     */
    public function getRefreshFormatted(): string
    {
        return $this->formatTime($this->refresh);
    }

    /**
     * Get retry interval in human readable format
     */
    public function getRetryFormatted(): string
    {
        return $this->formatTime($this->retry);
    }

    /**
     * Get expire time in human readable format
     */
    public function getExpireFormatted(): string
    {
        return $this->formatTime($this->expire);
    }

    /**
     * Get minimum TTL in human readable format
     */
    public function getMinimumFormatted(): string
    {
        return $this->formatTime($this->minimum);
    }

    /**
     * Format time in seconds to human readable format
     */
    private function formatTime(int $seconds): string
    {
        if ($seconds < 60) {
            return $seconds . "s";
        } elseif ($seconds < 3600) {
            return round($seconds / 60) . "m";
        } elseif ($seconds < 86400) {
            return round($seconds / 3600) . "h";
        } else {
            return round($seconds / 86400) . "d";
        }
    }

    /**
     * Check if the serial number indicates a newer version than another SOA record
     */
    public function isNewerThan(SoaRecord $other): bool
    {
        // Handle serial number comparison with wrap-around (RFC 1982)
        $diff = $this->serial - $other->serial;

        // If the difference is positive and less than 2^31, this is newer
        // If the difference is negative and greater than -2^31, this is older
        return $diff > 0 && $diff < 2147483648; // 2^31
    }

    /**
     * Check if this SOA record is older than another
     */
    public function isOlderThan(SoaRecord $other): bool
    {
        return $other->isNewerThan($this);
    }

    /**
     * Convert to array format for backwards compatibility
     */
    public function toArray(): array
    {
        return [
            "success" => $this->success,
            "error" => $this->error,
            "primary" => $this->primary,
            "admin" => $this->admin,
            "serial" => $this->serial,
            "refresh" => $this->refresh,
            "retry" => $this->retry,
            "expire" => $this->expire,
            "minimum" => $this->minimum,
        ];
    }

    /**
     * Create from array data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data["success"] ?? false,
            $data["error"] ?? null,
            $data["primary"] ?? "",
            $data["admin"] ?? "",
            $data["serial"] ?? 0,
            $data["refresh"] ?? 0,
            $data["retry"] ?? 0,
            $data["expire"] ?? 0,
            $data["minimum"] ?? 0
        );
    }

    /**
     * String representation
     */
    public function __toString(): string
    {
        if (!$this->success) {
            return "SOA Record Error: " . ($this->error ?? "Unknown error");
        }

        return "SOA {$this->primary} {$this->admin} {$this->serial} {$this->refresh} {$this->retry} {$this->expire} {$this->minimum}";
    }
}
