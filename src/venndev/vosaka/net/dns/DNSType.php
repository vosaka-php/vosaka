<?php

declare(strict_types=1);

namespace venndev\vosaka\net\dns;

/**
 * DNS Record Type Enumeration
 *
 * This enum defines all standard DNS record types as specified in various RFCs.
 * Each DNS record type has a specific numeric code used in DNS queries and responses.
 * This enum provides type safety and convenience methods for working with DNS record types.
 *
 * Supported Record Types:
 * - A: IPv4 address record (RFC 1035)
 * - NS: Name server record (RFC 1035)
 * - CNAME: Canonical name record (RFC 1035)
 * - SOA: Start of authority record (RFC 1035)
 * - PTR: Pointer record for reverse DNS (RFC 1035)
 * - MX: Mail exchange record (RFC 1035)
 * - TXT: Text record (RFC 1035)
 * - AAAA: IPv6 address record (RFC 3596)
 * - SRV: Service record (RFC 2782)
 * - DS: Delegation Signer for DNSSEC (RFC 4034)
 * - RRSIG: Resource Record Signature for DNSSEC (RFC 4034)
 * - NSEC: Next Secure record for DNSSEC (RFC 4034)
 * - DNSKEY: DNS Key record for DNSSEC (RFC 4034)
 * - NSEC3: Next Secure version 3 for DNSSEC (RFC 5155)
 * - IXFR: Incremental zone transfer (RFC 1995)
 * - AXFR: Authoritative zone transfer (RFC 1035)
 * - ANY: Query for any record type (RFC 1035)
 * - CAA: Certification Authority Authorization (RFC 6844)
 *
 * @see https://www.iana.org/assignments/dns-parameters/dns-parameters.xhtml
 */
enum DNSType: int
{
    /** IPv4 address record - Maps a domain name to an IPv4 address */
    case A = 1;

    /** Name server record - Specifies the authoritative name servers for a domain */
    case NS = 2;

    /** Canonical name record - Creates an alias from one domain name to another */
    case CNAME = 5;

    /** Start of authority record - Contains administrative information about the domain */
    case SOA = 6;

    /** Pointer record - Maps an IP address to a domain name (reverse DNS) */
    case PTR = 12;

    /** Mail exchange record - Specifies mail servers for a domain */
    case MX = 15;

    /** Text record - Contains arbitrary text data */
    case TXT = 16;

    /** IPv6 address record - Maps a domain name to an IPv6 address */
    case AAAA = 28;

    /** Service record - Specifies location of services */
    case SRV = 33;

    /** Delegation Signer record - Used in DNSSEC to identify a key signing key */
    case DS = 43;

    /** Resource Record Signature - Contains DNSSEC signature data */
    case RRSIG = 46;

    /** Next Secure record - Used in DNSSEC to prove non-existence of records */
    case NSEC = 47;

    /** DNS Key record - Contains public key for DNSSEC */
    case DNSKEY = 48;

    /** Next Secure version 3 - Enhanced version of NSEC for DNSSEC */
    case NSEC3 = 50;

    /** Incremental zone transfer - Requests incremental zone updates */
    case IXFR = 251;

    /** Authoritative zone transfer - Requests complete zone transfer */
    case AXFR = 252;

    /** Query for any record type - Wildcard query type */
    case ANY = 255;

    /** Certification Authority Authorization - Specifies which CAs can issue certificates */
    case CAA = 257;

    /**
     * Create DNSType from string name
     *
     * This method allows creating a DNSType enum instance from a string representation
     * of the record type name. The comparison is case-insensitive.
     *
     * @param string $name DNS record type name (e.g., 'A', 'AAAA', 'MX')
     * @return self|null Returns DNSType instance or null if name is invalid
     *
     * @example
     * $type = DNSType::fromName('A'); // Returns DNSType::A
     * $type = DNSType::fromName('mx'); // Returns DNSType::MX (case-insensitive)
     * $type = DNSType::fromName('INVALID'); // Returns null
     */
    public static function fromName(string $name): ?self
    {
        $upperName = strtoupper($name);

        foreach (self::cases() as $case) {
            if ($case->name === $upperName) {
                return $case;
            }
        }

        return null;
    }

    /**
     * Create DNSType from numeric value
     *
     * This method creates a DNSType enum instance from its numeric DNS record type code.
     * This is useful when parsing DNS responses that contain numeric type codes.
     *
     * @param int $value DNS record type numeric code
     * @return self|null Returns DNSType instance or null if value is invalid
     *
     * @example
     * $type = DNSType::fromValue(1); // Returns DNSType::A
     * $type = DNSType::fromValue(28); // Returns DNSType::AAAA
     * $type = DNSType::fromValue(999); // Returns null
     */
    public static function fromValue(int $value): ?self
    {
        return self::tryFrom($value);
    }

    /**
     * Get all DNS types as associative array
     *
     * Returns an associative array mapping DNS record type names to their numeric codes.
     * This is useful for generating lookup tables or for debugging purposes.
     *
     * @return array<string, int> Array mapping type names to numeric codes
     *
     * @example
     * $types = DNSType::toArray();
     * // Returns: ['A' => 1, 'NS' => 2, 'CNAME' => 5, ...]
     */
    public static function toArray(): array
    {
        $result = [];
        foreach (self::cases() as $case) {
            $result[$case->name] = $case->value;
        }
        return $result;
    }

    /**
     * Get DNS type name from numeric value
     *
     * This method returns the string name of a DNS record type given its numeric code.
     * This is the inverse operation of getting the numeric value from a name.
     *
     * @param int $value DNS record type numeric code
     * @return string|null Returns type name or null if value is invalid
     *
     * @example
     * $name = DNSType::nameFromValue(1); // Returns 'A'
     * $name = DNSType::nameFromValue(28); // Returns 'AAAA'
     * $name = DNSType::nameFromValue(999); // Returns null
     */
    public static function nameFromValue(int $value): ?string
    {
        return self::fromValue($value)?->name;
    }

    /**
     * Check if this is a query-only type
     *
     * Some DNS record types are only used in queries and cannot appear in zone files
     * or as actual resource records. This method identifies such types.
     *
     * @return bool True if this is a query-only type
     *
     * @example
     * DNSType::A->isQueryOnly(); // Returns false
     * DNSType::ANY->isQueryOnly(); // Returns true
     * DNSType::AXFR->isQueryOnly(); // Returns true
     */
    public function isQueryOnly(): bool
    {
        return match ($this) {
            self::IXFR, self::AXFR, self::ANY => true,
            default => false,
        };
    }

    /**
     * Check if this is a DNSSEC-related record type
     *
     * This method identifies DNS record types that are specifically used for
     * DNSSEC (DNS Security Extensions) functionality.
     *
     * @return bool True if this is a DNSSEC record type
     *
     * @example
     * DNSType::A->isDNSsecType(); // Returns false
     * DNSType::DNSKEY->isDNSsecType(); // Returns true
     * DNSType::RRSIG->isDNSsecType(); // Returns true
     */
    public function isDNSsecType(): bool
    {
        return match ($this) {
            self::DS,
            self::RRSIG,
            self::NSEC,
            self::DNSKEY,
            self::NSEC3
                => true,
            default => false,
        };
    }

    /**
     * Get human-readable description of the DNS record type
     *
     * Returns a descriptive string explaining what this DNS record type is used for.
     * This is useful for user interfaces and debugging.
     *
     * @return string Human-readable description
     *
     * @example
     * DNSType::A->getDescription(); // Returns "IPv4 address record"
     * DNSType::MX->getDescription(); // Returns "Mail exchange record"
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::A => "IPv4 address record",
            self::NS => "Name server record",
            self::CNAME => "Canonical name record",
            self::SOA => "Start of authority record",
            self::PTR => "Pointer record for reverse DNS",
            self::MX => "Mail exchange record",
            self::TXT => "Text record",
            self::AAAA => "IPv6 address record",
            self::SRV => "Service record",
            self::DS => "Delegation Signer record for DNSSEC",
            self::RRSIG => "Resource Record Signature for DNSSEC",
            self::NSEC => "Next Secure record for DNSSEC",
            self::DNSKEY => "DNS Key record for DNSSEC",
            self::NSEC3 => "Next Secure version 3 for DNSSEC",
            self::IXFR => "Incremental zone transfer",
            self::AXFR => "Authoritative zone transfer",
            self::ANY => "Query for any record type",
            self::CAA => "Certification Authority Authorization",
        };
    }
}
