***

# DNSClient

DNS Client for asynchronous DNS queries with support for UDP and TCP protocols

This class provides functionality to perform DNS queries asynchronously with
support for multiple DNS record types, DNSSEC validation, and EDNS extensions.
It implements automatic fallback from UDP to TCP when responses are truncated.

Features:
- Asynchronous DNS queries using generators
- Support for UDP and TCP protocols
- Automatic TCP fallback for truncated responses
- DNSSEC validation support
- EDNS(0) extensions
- Multiple DNS record type support
- Configurable timeout and buffer size

* Full name: `\venndev\vosaka\net\DNS\DNSClient`
* This class is marked as **final** and can't be subclassed
* This class is a **Final class**



## Properties


### timeout



```php
private int $timeout
```






***

### enableDNSsec



```php
private bool $enableDNSsec
```






***

### bufferSize



```php
private int $bufferSize
```






***

## Methods


### __construct

Initialize DNS client with configuration options

```php
public __construct(int $timeout = 10, bool $enableDNSsec = false, bool $enableEDNS = false, int $bufferSize = 4096): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$timeout` | **int** | Query timeout in seconds (default: 10) |
| `$enableDNSsec` | **bool** | Enable DNSSEC validation (default: false) |
| `$enableEDNS` | **bool** | Enable EDNS(0) extensions (default: false) |
| `$bufferSize` | **int** | Buffer size for receiving responses in bytes (default: 4096) |





***

### asyncDNSQuery

Perform asynchronous DNS queries for multiple hostnames

```php
public asyncDNSQuery(array{hostname: string, type?: string, server?: string}[] $queries): \venndev\vosaka\core\Result&lt;mixed,array{hostname: string, type: string, server: string, protocol: string, records: array, DNSsec?: array}[]&gt;
```

This method processes multiple DNS queries concurrently using UDP protocol
with automatic fallback to TCP for truncated responses. It returns a generator
that yields control back to the caller during processing.






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$queries` | **array{hostname: string, type?: string, server?: string}[]** | Array of query configurations |





***

### initUDPQuery

Initialize UDP socket and send DNS query

```php
private initUDPQuery(array{hostname: string, type?: string, server?: string} $query, array&lt;string,\Socket&gt;& $sockets, array&lt;string,array{hostname: string, type: string, id: int, server: string, protocol: string, query?: string}&gt;& $queryMap): \Generator&lt;mixed&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$query` | **array{hostname: string, type?: string, server?: string}** | Query configuration |
| `$sockets` | **array<string,\Socket>** | Reference to sockets array |
| `$queryMap` | **array<string,array{hostname: string, type: string, id: int, server: string, protocol: string, query?: string}>** | Reference to query mapping array |





***

### initializeTcpQuery

Initialize TCP socket and send DNS query

```php
private initializeTcpQuery(array{hostname: string, type: string, id: int, server: string} $query, array&lt;string,\Socket&gt;& $tcpSockets, array&lt;string,array{hostname: string, type: string, id: int, server: string, protocol: string}&gt;& $queryMap): \Generator&lt;mixed&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$query` | **array{hostname: string, type: string, id: int, server: string}** | Query configuration |
| `$tcpSockets` | **array<string,\Socket>** | Reference to TCP sockets array |
| `$queryMap` | **array<string,array{hostname: string, type: string, id: int, server: string, protocol: string}>** | Reference to query mapping array |





***

### handleUdpResponses

Handle UDP responses and process truncated responses

```php
private handleUdpResponses(array&lt;string,\Socket&gt;& $sockets, array&lt;string,array{hostname: string, type: string, id: int, server: string, protocol: string, query?: string}&gt; $queryMap, array{hostname: string, type: string, server: string, protocol: string, records: array, DNSsec?: array}[]& $results, array{hostname: string, type: string, id: int, server: string}[]& $tcpFallbacks): \Generator&lt;mixed&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$sockets` | **array<string,\Socket>** | Reference to UDP sockets array |
| `$queryMap` | **array<string,array{hostname: string, type: string, id: int, server: string, protocol: string, query?: string}>** | Query mapping array |
| `$results` | **array{hostname: string, type: string, server: string, protocol: string, records: array, DNSsec?: array}[]** | Reference to results array |
| `$tcpFallbacks` | **array{hostname: string, type: string, id: int, server: string}[]** | Reference to TCP fallback array |





***

### handleTcpResponses

Handle TCP responses and parse DNS data

```php
private handleTcpResponses(array&lt;string,\Socket&gt;& $tcpSockets, array&lt;string,array{hostname: string, type: string, id: int, server: string, protocol: string}&gt; $queryMap, array{hostname: string, type: string, server: string, protocol: string, records: array, DNSsec?: array}[]& $results): \Generator&lt;mixed&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$tcpSockets` | **array<string,\Socket>** | Reference to TCP sockets array |
| `$queryMap` | **array<string,array{hostname: string, type: string, id: int, server: string, protocol: string}>** | Query mapping array |
| `$results` | **array{hostname: string, type: string, server: string, protocol: string, records: array, DNSsec?: array}[]** | Reference to results array |





***

### createDNSQuery

Create DNS query packet in wire format

```php
private createDNSQuery(string $hostname, string $type, int $queryId, bool $isTcp = false): \Generator&lt;string&gt;
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$hostname` | **string** | The hostname to query |
| `$type` | **string** | DNS record type (A, AAAA, MX, etc.) |
| `$queryId` | **int** | Unique query identifier |
| `$isTcp` | **bool** | Whether this is a TCP query |


**Return Value:**

Binary DNS query packet




***

### createEDNSRecord

Create EDNS(0) OPT record for extended DNS functionality

```php
private createEDNSRecord(): string
```









**Return Value:**

Binary EDNS OPT record




***

### reverseIpForPtr

Convert IP address to reverse DNS format for PTR queries

```php
private reverseIpForPtr(string $ip): string
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$ip` | **string** | IP address (IPv4 or IPv6) |


**Return Value:**

Reversed IP format for PTR queries




***

### isTruncated

Check if DNS response is truncated (TC bit set)

```php
private isTruncated(string $response): bool
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$response` | **string** | Binary DNS response |


**Return Value:**

True if response is truncated




***

### parseDNSResponse

Parse DNS response and extract records

```php
private parseDNSResponse(string $response, int $expectedId, string $queryType): array{name: string, type: string, class: int, ttl: int, data: mixed, raw_type: int, section: string}[]
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$response` | **string** | Binary DNS response |
| `$expectedId` | **int** | Expected query ID |
| `$queryType` | **string** | Query type for validation |


**Return Value:**

Parsed DNS records




***

### parseRecord

Parse individual DNS record from response

```php
private parseRecord(string $response, int $offset): \venndev\vosaka\net\DNS\model\Record|null
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$response` | **string** | Binary DNS response |
| `$offset` | **int** | Current offset in response |





***

### parseRecordData

Parse record data based on DNS record type

```php
private parseRecordData(string $response, int $offset, int $type, int $length): \venndev\vosaka\net\DNS\model\AddressRecord|\venndev\vosaka\net\DNS\model\MxRecord|\venndev\vosaka\net\DNS\model\TxtRecord|\venndev\vosaka\net\DNS\model\SrvRecord|\venndev\vosaka\net\DNS\model\SoaRecord|\venndev\vosaka\net\DNS\model\NameRecord|\venndev\vosaka\net\DNS\model\RawRecord
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$response` | **string** | DNS response data |
| `$offset` | **int** | Current offset in response |
| `$type` | **int** | DNS record type |
| `$length` | **int** | Data length |


**Return Value:**

Structured parsed record data object




***

### parseTxtRecord

Parse TXT record data

```php
private parseTxtRecord(string $response, int $offset, int $length): \venndev\vosaka\net\DNS\model\TxtRecord
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$response` | **string** | DNS response data |
| `$offset` | **int** | Current offset in response |
| `$length` | **int** | Data length |


**Return Value:**

Structured TXT record data object




***

### parseSrvRecord

Parse SRV record data

```php
private parseSrvRecord(string $response, int $offset): \venndev\vosaka\net\DNS\model\SrvRecord
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$response` | **string** | DNS response data |
| `$offset` | **int** | Current offset in response |


**Return Value:**

Structured SRV record data object




***

### parseSoaRecord

Parse SOA record data

```php
private parseSoaRecord(string $response, int $offset): \venndev\vosaka\net\DNS\model\SoaRecord
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$response` | **string** | DNS response data |
| `$offset` | **int** | Current offset in response |


**Return Value:**

Structured SOA record data object




***

### validateDNSsec

Validate DNSSEC signatures and keys

```php
private validateDNSsec(array{name: string, type: string, class: int, ttl: int, data: mixed, raw_type: int, section: string}[] $records): array{status: string, signatures: array, keys: array, ds_records: array}
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$records` | **array{name: string, type: string, class: int, ttl: int, data: mixed, raw_type: int, section: string}[]** | DNS records to validate |


**Return Value:**

DNSSEC validation results




***

### readDNSName

Read DNS name from response with compression support

```php
private readDNSName(string $response, int $offset): string
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$response` | **string** | Binary DNS response |
| `$offset` | **int** | Current offset in response |


**Return Value:**

Parsed DNS name




***

### skipDNSName

Skip DNS name in response and return next offset

```php
private skipDNSName(string $response, int $offset): int
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$response` | **string** | Binary DNS response |
| `$offset` | **int** | Current offset in response |


**Return Value:**

Next offset after DNS name




***


***
> Automatically generated on 2025-07-04
