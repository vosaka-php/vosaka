<?php

declare(strict_types=1);

namespace venndev\vosaka\net\dns;

/**
 * DNSSEC Algorithm Enumeration
 *
 * This enum defines cryptographic algorithms used in DNSSEC (Domain Name System Security Extensions)
 * for digital signatures and key generation. Each algorithm has a specific numeric identifier as
 * defined by IANA and various RFCs.
 *
 * DNSSEC uses these algorithms to:
 * - Sign DNS resource records (RRSIG records)
 * - Generate and validate cryptographic keys (DNSKEY records)
 * - Create delegation signer records (DS records)
 * - Ensure data integrity and authenticity in DNS responses
 *
 * Algorithm Categories:
 * - RSA-based: RSAMD5, RSASHA1, RSASHA1_NSEC3_SHA1, RSASHA256, RSASHA512
 * - DSA-based: DSA, DSA_NSEC3_SHA1
 * - ECDSA-based: ECDSAP256SHA256, ECDSAP384SHA384
 * - EdDSA-based: ED25519, ED448
 * - GOST-based: ECC_GOST
 *
 * Security Considerations:
 * - RSAMD5 and DSA are considered deprecated due to security vulnerabilities
 * - RSASHA1 is being phased out in favor of stronger hash algorithms
 * - RSASHA256, RSASHA512, ECDSA, and EdDSA algorithms are recommended for new deployments
 *
 * @see https://www.iana.org/assignments/dns-sec-alg-numbers/dns-sec-alg-numbers.xhtml
 * @see RFC 4034 - Resource Records for the DNS Security Extensions
 * @see RFC 5702 - Use of SHA-2 Algorithms with RSA in DNSKEY and RRSIG Resource Records
 * @see RFC 6605 - Elliptic Curve Digital Signature Algorithm (DSA) for DNSSEC
 * @see RFC 8080 - Edwards-Curve Digital Security Algorithm (EdDSA) for DNSSEC
 */
enum DNSSecAlgorithm: int
{
    /**
     * RSA with MD5 hash algorithm (DEPRECATED)
     * @deprecated This algorithm is deprecated due to MD5 vulnerabilities
     * @see RFC 3110
     */
    case RSAMD5 = 1;

    /**
     * Digital Signature Algorithm (DSA) with SHA-1 (DEPRECATED)
     * @deprecated This algorithm is deprecated due to security weaknesses
     * @see RFC 2536
     */
    case DSA = 3;

    /**
     * RSA with SHA-1 hash algorithm
     * @see RFC 3110
     */
    case RSASHA1 = 5;

    /**
     * DSA with SHA-1 for NSEC3 records
     * @see RFC 5155
     */
    case DSA_NSEC3_SHA1 = 6;

    /**
     * RSA with SHA-1 for NSEC3 records
     * @see RFC 5155
     */
    case RSASHA1_NSEC3_SHA1 = 7;

    /**
     * RSA with SHA-256 hash algorithm (RECOMMENDED)
     * This is a widely supported and secure algorithm for DNSSEC
     * @see RFC 5702
     */
    case RSASHA256 = 8;

    /**
     * RSA with SHA-512 hash algorithm (RECOMMENDED)
     * Provides stronger security than SHA-256 at the cost of larger signatures
     * @see RFC 5702
     */
    case RSASHA512 = 10;

    /**
     * Elliptic Curve Cryptography with GOST R 34.10-2001
     * Russian national standard cryptographic algorithm
     * @see RFC 5933
     */
    case ECC_GOST = 12;

    /**
     * Elliptic Curve DSA with P-256 curve and SHA-256 (RECOMMENDED)
     * Provides equivalent security to RSA-2048 with smaller key sizes
     * @see RFC 6605
     */
    case ECDSAP256SHA256 = 13;

    /**
     * Elliptic Curve DSA with P-384 curve and SHA-384 (RECOMMENDED)
     * Provides equivalent security to RSA-3072 with smaller key sizes
     * @see RFC 6605
     */
    case ECDSAP384SHA384 = 14;

    /**
     * Edwards-curve Digital Signature Algorithm using Ed25519 (RECOMMENDED)
     * Modern elliptic curve algorithm with excellent performance and security
     * @see RFC 8080
     */
    case ED25519 = 15;

    /**
     * Edwards-curve Digital Signature Algorithm using Ed448 (RECOMMENDED)
     * Provides higher security level than Ed25519 at the cost of performance
     * @see RFC 8080
     */
    case ED448 = 16;

    /**
     * Create DNSSecAlgorithm from numeric value
     *
     * This method creates a DNSSecAlgorithm enum instance from its numeric algorithm identifier.
     * This is useful when parsing DNSSEC records that contain algorithm numbers.
     *
     * @param int $value DNSSEC algorithm numeric identifier
     * @return self|null Returns DNSSecAlgorithm instance or null if value is invalid
     *
     * @example
     * $algo = DNSSecAlgorithm::fromValue(8); // Returns DNSSecAlgorithm::RSASHA256
     * $algo = DNSSecAlgorithm::fromValue(13); // Returns DNSSecAlgorithm::ECDSAP256SHA256
     * $algo = DNSSecAlgorithm::fromValue(999); // Returns null
     */
    public static function fromValue(int $value): ?self
    {
        return self::tryFrom($value);
    }

    /**
     * Get all DNSSEC algorithms as associative array
     *
     * Returns an associative array mapping algorithm numeric identifiers to their names.
     * This is useful for generating lookup tables or for debugging purposes.
     *
     * @return array<int, string> Array mapping algorithm codes to names
     *
     * @example
     * $algorithms = DNSSecAlgorithm::toArray();
     * // Returns: [1 => 'RSAMD5', 3 => 'DSA', 5 => 'RSASHA1', ...]
     */
    public static function toArray(): array
    {
        $result = [];
        foreach (self::cases() as $case) {
            $result[$case->value] = $case->name;
        }
        return $result;
    }

    /**
     * Get algorithm name from numeric value
     *
     * This method returns the string name of a DNSSEC algorithm given its numeric identifier.
     * This is useful when displaying algorithm information in human-readable format.
     *
     * @param int $value DNSSEC algorithm numeric identifier
     * @return string|null Returns algorithm name or null if value is invalid
     *
     * @example
     * $name = DNSSecAlgorithm::nameFromValue(8); // Returns 'RSASHA256'
     * $name = DNSSecAlgorithm::nameFromValue(15); // Returns 'ED25519'
     * $name = DNSSecAlgorithm::nameFromValue(999); // Returns null
     */
    public static function nameFromValue(int $value): ?string
    {
        return self::fromValue($value)?->name;
    }

    /**
     * Get all recommended algorithms for new deployments
     *
     * Returns an array of DNSSEC algorithms that are currently recommended
     * for new DNSSEC deployments based on current security standards.
     *
     * @return array<self> Array of recommended DNSSecAlgorithm instances
     *
     * @example
     * $recommended = DNSSecAlgorithm::getRecommended();
     * // Returns algorithms like RSASHA256, RSASHA512, ECDSAP256SHA256, etc.
     */
    public static function getRecommended(): array
    {
        return [
            self::RSASHA256,
            self::RSASHA512,
            self::ECDSAP256SHA256,
            self::ECDSAP384SHA384,
            self::ED25519,
            self::ED448,
        ];
    }

    /**
     * Get all deprecated algorithms that should be avoided
     *
     * Returns an array of DNSSEC algorithms that are considered deprecated
     * or insecure and should not be used for new deployments.
     *
     * @return array<self> Array of deprecated DNSSecAlgorithm instances
     *
     * @example
     * $deprecated = DNSSecAlgorithm::getDeprecated();
     * // Returns algorithms like RSAMD5, DSA
     */
    public static function getDeprecated(): array
    {
        return [self::RSAMD5, self::DSA];
    }

    /**
     * Check if this algorithm is considered secure for current use
     *
     * Determines whether this DNSSEC algorithm is considered secure enough
     * for current deployments based on modern cryptographic standards.
     *
     * @return bool True if algorithm is considered secure
     *
     * @example
     * DNSSecAlgorithm::RSAMD5->isSecure(); // Returns false (deprecated)
     * DNSSecAlgorithm::RSASHA256->isSecure(); // Returns true (recommended)
     * DNSSecAlgorithm::ED25519->isSecure(); // Returns true (modern)
     */
    public function isSecure(): bool
    {
        return match ($this) {
            self::RSAMD5, self::DSA => false,
            default => true,
        };
    }

    /**
     * Check if this algorithm is recommended for new deployments
     *
     * Determines whether this DNSSEC algorithm is actively recommended
     * for new DNSSEC deployments.
     *
     * @return bool True if algorithm is recommended
     *
     * @example
     * DNSSecAlgorithm::RSASHA1->isRecommended(); // Returns false (legacy)
     * DNSSecAlgorithm::RSASHA256->isRecommended(); // Returns true
     * DNSSecAlgorithm::ED25519->isRecommended(); // Returns true
     */
    public function isRecommended(): bool
    {
        return in_array($this, self::getRecommended(), true);
    }

    /**
     * Check if this algorithm is deprecated
     *
     * Determines whether this DNSSEC algorithm is deprecated and should
     * be avoided in new deployments.
     *
     * @return bool True if algorithm is deprecated
     *
     * @example
     * DNSSecAlgorithm::RSAMD5->isDeprecated(); // Returns true
     * DNSSecAlgorithm::DSA->isDeprecated(); // Returns true
     * DNSSecAlgorithm::RSASHA256->isDeprecated(); // Returns false
     */
    public function isDeprecated(): bool
    {
        return in_array($this, self::getDeprecated(), true);
    }

    /**
     * Get the cryptographic family of this algorithm
     *
     * Returns the underlying cryptographic algorithm family (RSA, DSA, ECDSA, EdDSA, GOST).
     *
     * @return string Cryptographic algorithm family
     *
     * @example
     * DNSSecAlgorithm::RSASHA256->getFamily(); // Returns 'RSA'
     * DNSSecAlgorithm::ECDSAP256SHA256->getFamily(); // Returns 'ECDSA'
     * DNSSecAlgorithm::ED25519->getFamily(); // Returns 'EdDSA'
     */
    public function getFamily(): string
    {
        return match ($this) {
            self::RSAMD5,
            self::RSASHA1,
            self::RSASHA1_NSEC3_SHA1,
            self::RSASHA256,
            self::RSASHA512
                => "RSA",
            self::DSA, self::DSA_NSEC3_SHA1 => "DSA",
            self::ECDSAP256SHA256, self::ECDSAP384SHA384 => "ECDSA",
            self::ED25519, self::ED448 => "EdDSA",
            self::ECC_GOST => "GOST",
        };
    }

    /**
     * Get the hash algorithm used by this DNSSEC algorithm
     *
     * Returns the hash algorithm (digest function) used in conjunction
     * with the cryptographic algorithm.
     *
     * @return string Hash algorithm name
     *
     * @example
     * DNSSecAlgorithm::RSASHA256->getHashAlgorithm(); // Returns 'SHA-256'
     * DNSSecAlgorithm::ECDSAP384SHA384->getHashAlgorithm(); // Returns 'SHA-384'
     * DNSSecAlgorithm::ED25519->getHashAlgorithm(); // Returns 'SHA-512' (internal)
     */
    public function getHashAlgorithm(): string
    {
        return match ($this) {
            self::RSAMD5 => "MD5",
            self::DSA,
            self::RSASHA1,
            self::DSA_NSEC3_SHA1,
            self::RSASHA1_NSEC3_SHA1
                => "SHA-1",
            self::RSASHA256, self::ECDSAP256SHA256 => "SHA-256",
            self::ECDSAP384SHA384 => "SHA-384",
            self::RSASHA512 => "SHA-512",
            self::ED25519, self::ED448 => "SHA-512",
            self::ECC_GOST => "GOST R 34.11-94",
        };
    }

    /**
     * Get human-readable description of the DNSSEC algorithm
     *
     * Returns a descriptive string explaining this DNSSEC algorithm,
     * including its security status and use cases.
     *
     * @return string Human-readable description
     *
     * @example
     * DNSSecAlgorithm::RSASHA256->getDescription();
     * // Returns "RSA with SHA-256 hash algorithm (RECOMMENDED)"
     */
    public function getDescription(): string
    {
        $status = $this->isDeprecated()
            ? " (DEPRECATED)"
            : ($this->isRecommended()
                ? " (RECOMMENDED)"
                : "");

        return match ($this) {
            self::RSAMD5 => "RSA with MD5 hash algorithm",
            self::DSA => "Digital Signature Algorithm with SHA-1",
            self::RSASHA1 => "RSA with SHA-1 hash algorithm",
            self::DSA_NSEC3_SHA1 => "DSA with SHA-1 for NSEC3 records",
            self::RSASHA1_NSEC3_SHA1 => "RSA with SHA-1 for NSEC3 records",
            self::RSASHA256 => "RSA with SHA-256 hash algorithm",
            self::RSASHA512 => "RSA with SHA-512 hash algorithm",
            self::ECC_GOST
                => "Elliptic Curve Cryptography with GOST R 34.10-2001",
            self::ECDSAP256SHA256
                => "Elliptic Curve DSA with P-256 curve and SHA-256",
            self::ECDSAP384SHA384
                => "Elliptic Curve DSA with P-384 curve and SHA-384",
            self::ED25519
                => "Edwards-curve Digital Signature Algorithm using Ed25519",
            self::ED448
                => "Edwards-curve Digital Signature Algorithm using Ed448",
        } . $status;
    }

    /**
     * Get typical key size for this algorithm
     *
     * Returns the typical or recommended key size in bits for this algorithm.
     * For elliptic curve algorithms, this represents the curve size.
     *
     * @return int Key size in bits
     *
     * @example
     * DNSSecAlgorithm::RSASHA256->getTypicalKeySize(); // Returns 2048
     * DNSSecAlgorithm::ECDSAP256SHA256->getTypicalKeySize(); // Returns 256
     * DNSSecAlgorithm::ED25519->getTypicalKeySize(); // Returns 255
     */
    public function getTypicalKeySize(): int
    {
        return match ($this) {
            self::RSAMD5,
            self::RSASHA1,
            self::RSASHA1_NSEC3_SHA1,
            self::RSASHA256
                => 2048,
            self::RSASHA512 => 2048, // Can be larger but 2048 is common
            self::DSA, self::DSA_NSEC3_SHA1 => 1024,
            self::ECDSAP256SHA256 => 256,
            self::ECDSAP384SHA384 => 384,
            self::ED25519 => 255, // Technically 255.x bits
            self::ED448 => 448,
            self::ECC_GOST => 256,
        };
    }
}
