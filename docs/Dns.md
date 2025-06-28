# DNS Module Documentation

## Overview

The DNS module provides a comprehensive, asynchronous DNS client implementation for PHP with support for multiple DNS record types, DNSSEC validation, and both UDP and TCP protocols. This module is designed for high-performance applications that need to perform multiple DNS queries concurrently.

## Features

- **Asynchronous DNS Queries**: Perform multiple DNS queries concurrently using PHP generators
- **Protocol Support**: UDP with automatic TCP fallback for truncated responses
- **DNSSEC Support**: Built-in DNSSEC validation capabilities
- **EDNS(0) Extensions**: Support for Extended DNS functionality
- **Multiple Record Types**: Support for A, AAAA, MX, TXT, SRV, SOA, CNAME, NS, PTR, and DNSSEC records
- **Type Safety**: Full PHP type hints and comprehensive documentation
- **Configurable**: Customizable timeout, buffer size, and security options

## Classes

### DNSClient

The main DNS client class that handles asynchronous DNS queries.

#### Constructor Parameters

- `int $timeout` - Query timeout in seconds (default: 10)
- `bool $enableDnssec` - Enable DNSSEC validation (default: false)
- `bool $enableEdns` - Enable EDNS(0) extensions (default: false)
- `int $bufferSize` - Buffer size for receiving responses in bytes (default: 4096)

#### Methods

##### `asyncDnsQuery(array $queries): Generator`

Performs asynchronous DNS queries for multiple hostnames.

**Parameters:**
- `$queries` - Array of query configurations

**Query Configuration Format:**
```php
[
    'hostname' => 'example.com',    // Required: Domain to query
    'type' => 'A',                  // Optional: Record type (default: 'A')
    'server' => '8.8.8.8'           // Optional: DNS server (default: '8.8.8.8')
]
```

**Returns:** Generator yielding DNS query results

**Example:**
```php
use venndev\vosaka\net\dns\DNSClient;

$client = new DNSClient(timeout: 15, enableDnssec: true);

$queries = [
    ['hostname' => 'example.com', 'type' => 'A'],
    ['hostname' => 'google.com', 'type' => 'MX'],
    ['hostname' => 'cloudflare.com', 'type' => 'AAAA', 'server' => '1.1.1.1']
];

$generator = $client->asyncDnsQuery($queries);
$results = yield from $generator;

foreach ($results as $result) {
    echo "Host: {$result['hostname']}\n";
    echo "Type: {$result['type']}\n";
    echo "Server: {$result['server']}\n";
    echo "Protocol: {$result['protocol']}\n";
    
    foreach ($result['records'] as $record) {
        echo "  {$record['name']} -> {$record['data']}\n";
    }
}
```

### DNSType

Enumeration of DNS record types with utility methods.

#### Supported Record Types

| Type | Code | Description |
|------|------|-------------|
| A | 1 | IPv4 address record |
| NS | 2 | Name server record |
| CNAME | 5 | Canonical name record |
| SOA | 6 | Start of authority record |
| PTR | 12 | Pointer record for reverse DNS |
| MX | 15 | Mail exchange record |
| TXT | 16 | Text record |
| AAAA | 28 | IPv6 address record |
| SRV | 33 | Service record |
| DS | 43 | Delegation Signer for DNSSEC |
| RRSIG | 46 | Resource Record Signature for DNSSEC |
| NSEC | 47 | Next Secure record for DNSSEC |
| DNSKEY | 48 | DNS Key record for DNSSEC |
| NSEC3 | 50 | Next Secure version 3 for DNSSEC |
| IXFR | 251 | Incremental zone transfer |
| AXFR | 252 | Authoritative zone transfer |
| ANY | 255 | Query for any record type |
| CAA | 257 | Certification Authority Authorization |

#### Methods

##### `fromName(string $name): ?DNSType`

Create DNSType from string name (case-insensitive).

```php
$type = DNSType::fromName('A');     // Returns DNSType::A
$type = DNSType::fromName('mx');    // Returns DNSType::MX
$type = DNSType::fromName('INVALID'); // Returns null
```

##### `fromValue(int $value): ?DNSType`

Create DNSType from numeric value.

```php
$type = DNSType::fromValue(1);   // Returns DNSType::A
$type = DNSType::fromValue(28);  // Returns DNSType::AAAA
$type = DNSType::fromValue(999); // Returns null
```

##### `toArray(): array`

Get all DNS types as associative array.

```php
$types = DNSType::toArray();
// Returns: ['A' => 1, 'NS' => 2, 'CNAME' => 5, ...]
```

##### `isQueryOnly(): bool`

Check if this is a query-only type (AXFR, IXFR, ANY).

##### `isDnssecType(): bool`

Check if this is a DNSSEC-related record type.

##### `getDescription(): string`

Get human-readable description of the DNS record type.

### DNSSecAlgorithm

Enumeration of DNSSEC cryptographic algorithms.

#### Supported Algorithms

| Algorithm | Code | Family | Hash | Status |
|-----------|------|--------|------|--------|
| RSAMD5 | 1 | RSA | MD5 | DEPRECATED |
| DSA | 3 | DSA | SHA-1 | DEPRECATED |
| RSASHA1 | 5 | RSA | SHA-1 | Legacy |
| DSA_NSEC3_SHA1 | 6 | DSA | SHA-1 | Legacy |
| RSASHA1_NSEC3_SHA1 | 7 | RSA | SHA-1 | Legacy |
| RSASHA256 | 8 | RSA | SHA-256 | RECOMMENDED |
| RSASHA512 | 10 | RSA | SHA-512 | RECOMMENDED |
| ECC_GOST | 12 | GOST | GOST R 34.11-94 | Regional |
| ECDSAP256SHA256 | 13 | ECDSA | SHA-256 | RECOMMENDED |
| ECDSAP384SHA384 | 14 | ECDSA | SHA-384 | RECOMMENDED |
| ED25519 | 15 | EdDSA | SHA-512 | RECOMMENDED |
| ED448 | 16 | EdDSA | SHA-512 | RECOMMENDED |

#### Methods

##### `fromValue(int $value): ?DNSSecAlgorithm`

Create DNSSecAlgorithm from numeric value.

##### `getRecommended(): array`

Get all recommended algorithms for new deployments.

##### `getDeprecated(): array`

Get all deprecated algorithms that should be avoided.

##### `isSecure(): bool`

Check if this algorithm is considered secure for current use.

##### `isRecommended(): bool`

Check if this algorithm is recommended for new deployments.

##### `isDeprecated(): bool`

Check if this algorithm is deprecated.

##### `getFamily(): string`

Get the cryptographic family (RSA, DSA, ECDSA, EdDSA, GOST).

##### `getHashAlgorithm(): string`

Get the hash algorithm used by this DNSSEC algorithm.

##### `getDescription(): string`

Get human-readable description of the DNSSEC algorithm.

##### `getTypicalKeySize(): int`

Get typical key size for this algorithm in bits.

## Usage Examples

### Basic DNS Query

```php
use venndev\vosaka\net\dns\DNSClient;

$client = new DNSClient();
$queries = [
    ['hostname' => 'example.com', 'type' => 'A']
];

$generator = $client->asyncDnsQuery($queries);
$results = yield from $generator;

foreach ($results[0]['records'] as $record) {
    if ($record['type'] === 'A') {
        echo "IPv4 Address: {$record['data']}\n";
    }
}
```

### Multiple Queries with Different Types

```php
use venndev\vosaka\net\dns\DNSClient;

$client = new DNSClient(timeout: 20);

$queries = [
    ['hostname' => 'google.com', 'type' => 'A'],
    ['hostname' => 'google.com', 'type' => 'AAAA'],
    ['hostname' => 'google.com', 'type' => 'MX'],
    ['hostname' => 'google.com', 'type' => 'TXT'],
    ['hostname' => 'google.com', 'type' => 'NS']
];

$generator = $client->asyncDnsQuery($queries);
$results = yield from $generator;

foreach ($results as $result) {
    echo "\n=== {$result['hostname']} ({$result['type']}) ===\n";
    foreach ($result['records'] as $record) {
        echo "{$record['name']} -> ";
        if (is_array($record['data'])) {
            echo json_encode($record['data']);
        } else {
            echo $record['data'];
        }
        echo " (TTL: {$record['ttl']})\n";
    }
}
```

### DNSSEC-Enabled Queries

```php
use venndev\vosaka\net\dns\DNSClient;

$client = new DNSClient(
    timeout: 30,
    enableDnssec: true,
    enableEdns: true,
    bufferSize: 8192
);

$queries = [
    ['hostname' => 'dnssec-name.org', 'type' => 'A'],
    ['hostname' => 'dnssec-name.org', 'type' => 'DNSKEY'],
    ['hostname' => 'dnssec-name.org', 'type' => 'DS']
];

$generator = $client->asyncDnsQuery($queries);
$results = yield from $generator;

foreach ($results as $result) {
    echo "\n=== {$result['hostname']} ===\n";
    
    if (isset($result['dnssec'])) {
        echo "DNSSEC Status: {$result['dnssec']['status']}\n";
    }
    
    foreach ($result['records'] as $record) {
        echo "{$record['type']}: {$record['name']} -> ";
        if (is_array($record['data'])) {
            echo json_encode($record['data']);
        } else {
            echo $record['data'];
        }
        echo "\n";
    }
}
```

### Reverse DNS Lookup

```php
use venndev\vosaka\net\dns\DNSClient;

$client = new DNSClient();

$queries = [
    ['hostname' => '8.8.8.8', 'type' => 'PTR'],
    ['hostname' => '2001:4860:4860::8888', 'type' => 'PTR']
];

$generator = $client->asyncDnsQuery($queries);
$results = yield from $generator;

foreach ($results as $result) {
    foreach ($result['records'] as $record) {
        if ($record['type'] === 'PTR') {
            echo "Reverse DNS for {$result['hostname']}: {$record['data']}\n";
        }
    }
}
```

### Using Different DNS Servers

```php
use venndev\vosaka\net\dns\DNSClient;

$client = new DNSClient();

$queries = [
    ['hostname' => 'example.com', 'type' => 'A', 'server' => '8.8.8.8'],      // Google DNS
    ['hostname' => 'example.com', 'type' => 'A', 'server' => '1.1.1.1'],      // Cloudflare DNS
    ['hostname' => 'example.com', 'type' => 'A', 'server' => '208.67.222.222'] // OpenDNS
];

$generator = $client->asyncDnsQuery($queries);
$results = yield from $generator;

foreach ($results as $result) {
    echo "Server {$result['server']}: ";
    foreach ($result['records'] as $record) {
        if ($record['type'] === 'A') {
            echo "{$record['data']} ";
        }
    }
    echo "\n";
}
```

## Error Handling

The DNS client handles various error conditions gracefully:

- **Timeout**: Queries that exceed the timeout are automatically cancelled
- **Network Errors**: Socket errors are logged and the query is marked as failed
- **Invalid Responses**: Malformed DNS responses are detected and handled
- **Truncated Responses**: Automatically fall back from UDP to TCP when responses are truncated
- **DNSSEC Validation**: Invalid DNSSEC signatures are detected and reported

## Performance Considerations

- **Concurrent Queries**: The async nature allows multiple queries to be processed simultaneously
- **UDP First**: UDP is used initially for better performance, with TCP fallback when needed
- **Buffer Size**: Increase buffer size for domains with large DNS responses
- **Timeout**: Balance between responsiveness and allowing slow DNS servers to respond
- **Connection Reuse**: TCP connections are reused where possible to reduce overhead

## Security Features

### DNSSEC Support

When DNSSEC is enabled, the client:
- Validates RRSIG records against DNSKEY records
- Checks the chain of trust up to the root zone
- Verifies DS records in parent zones
- Reports validation status in query results

### EDNS(0) Extensions

EDNS(0) support provides:
- Larger UDP payload sizes (up to 4096 bytes by default)
- DNSSEC OK (DO) bit signaling
- Extended response codes
- Additional protocol features

## Dependencies

- PHP 8.0 or higher
- PHP Sockets extension
- venndev\vosaka\utils\Defer (for cleanup functionality)

## Standards Compliance

This DNS client implementation follows these RFC standards:

- **RFC 1035**: Domain Names - Implementation and Specification
- **RFC 2782**: A DNS RR for specifying the location of services (SRV)
- **RFC 3110**: RSA/SHA-1 SIGs and RSA KEYs in the Domain Name System
- **RFC 4034**: Resource Records for the DNS Security Extensions
- **RFC 5155**: DNS Security (DNSSEC) Hashed Authenticated Denial of Existence
- **RFC 5702**: Use of SHA-2 Algorithms with RSA in DNSKEY and RRSIG Resource Records
- **RFC 6605**: Elliptic Curve Digital Signature Algorithm (DSA) for DNSSEC
- **RFC 6844**: DNS Certification Authority Authorization (CAA) Resource Record
- **RFC 8080**: Edwards-Curve Digital Security Algorithm (EdDSA) for DNSSEC

## License

This DNS module is part of the VOsaka project and follows the same licensing terms.