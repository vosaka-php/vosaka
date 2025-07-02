<?php

declare(strict_types=1);

namespace venndev\vosaka\net\DNS;

use Generator;
use Socket;
use venndev\vosaka\core\Result;
use venndev\vosaka\core\Future;
use venndev\vosaka\net\DNS\exceptions\DNSException;
use venndev\vosaka\net\DNS\exceptions\DNSNetworkException;
use venndev\vosaka\net\DNS\exceptions\DNSQueryException;
use venndev\vosaka\net\DNS\exceptions\DNSTimeoutException;
use venndev\vosaka\net\DNS\exceptions\DNSParseException;
use venndev\vosaka\net\DNS\exceptions\DNSConfigurationException;
use venndev\vosaka\net\DNS\exceptions\DNSSECException;
use venndev\vosaka\net\DNS\model\Record;
use venndev\vosaka\net\DNS\model\AddressRecord;
use venndev\vosaka\net\DNS\model\MxRecord;
use venndev\vosaka\net\DNS\model\TxtRecord;
use venndev\vosaka\net\DNS\model\SrvRecord;
use venndev\vosaka\net\DNS\model\SoaRecord;
use venndev\vosaka\net\DNS\model\NameRecord;
use venndev\vosaka\net\DNS\model\RawRecord;
use venndev\vosaka\VOsaka;

/**
 * DNS Client for asynchronous DNS queries with support for UDP and TCP protocols
 *
 * This class provides functionality to perform DNS queries asynchronously with
 * support for multiple DNS record types, DNSSEC validation, and EDNS extensions.
 * It implements automatic fallback from UDP to TCP when responses are truncated.
 *
 * Features:
 * - Asynchronous DNS queries using generators
 * - Support for UDP and TCP protocols
 * - Automatic TCP fallback for truncated responses
 * - DNSSEC validation support
 * - EDNS(0) extensions
 * - Multiple DNS record type support
 * - Configurable timeout and buffer size
 */
final class DNSClient
{
    private int $timeout;
    private bool $enableDNSsec;
    private bool $enableEDNS;
    private int $bufferSize;

    /**
     * Initialize DNS client with configuration options
     *
     * @param int $timeout Query timeout in seconds (default: 10)
     * @param bool $enableDNSsec Enable DNSSEC validation (default: false)
     * @param bool $enableEDNS Enable EDNS(0) extensions (default: false)
     * @param int $bufferSize Buffer size for receiving responses in bytes (default: 4096)
     */
    public function __construct(
        int $timeout = 10,
        bool $enableDNSsec = false,
        bool $enableEDNS = false,
        int $bufferSize = 4096
    ) {
        if ($timeout <= 0) {
            throw new DNSConfigurationException(
                "Timeout must be greater than 0",
                "timeout",
                $timeout
            );
        }

        if ($bufferSize < 512) {
            throw new DNSConfigurationException(
                "Buffer size must be at least 512 bytes",
                "bufferSize",
                $bufferSize
            );
        }

        $this->timeout = $timeout;
        $this->enableDNSsec = $enableDNSsec;
        $this->enableEDNS = $enableEDNS;
        $this->bufferSize = $bufferSize;
    }

    /**
     * Perform asynchronous DNS queries for multiple hostnames
     *
     * This method processes multiple DNS queries concurrently using UDP protocol
     * with automatic fallback to TCP for truncated responses. It returns a generator
     * that yields control back to the caller during processing.
     *
     * @param array<array{hostname: string, type?: string, server?: string}> $queries Array of query configurations
     * @return Result<mixed, mixed, mixed, array<array{hostname: string, type: string, server: string, protocol: string, records: array, DNSsec?: array}>>
     *
     * @example
     * $queries = [
     *     ['hostname' => 'example.com', 'type' => 'A'],
     *     ['hostname' => 'google.com', 'type' => 'MX', 'server' => '1.1.1.1']
     * ];
     * $generator = $client->asyncDNSQuery($queries);
     * $results = yield from $generator;
     */
    public function asyncDNSQuery(array $queries): Result
    {
        $fn = function () use ($queries): Generator {
            /** @var array<string, Socket> $sockets */
            $sockets = [];
            /** @var array<string, Socket> $tcpSockets */
            $tcpSockets = [];
            /** @var array<string, array{hostname: string, type: string, id: int, server: string, protocol: string, query?: string}> $queryMap */
            $queryMap = [];
            /** @var array<array{hostname: string, type: string, server: string, protocol: string, records: array, DNSsec?: array}> $results */
            $results = [];

            // Initialize UDP queries
            foreach ($queries as $query) {
                yield from $this->initUDPQuery($query, $sockets, $queryMap);
            }

            $start = time();
            /** @var array<array{hostname: string, type: string, id: int, server: string}> $tcpFallbacks */
            $tcpFallbacks = [];

            // Process responses until timeout or all queries complete
            while (
                (! empty($sockets) || ! empty($tcpSockets)) &&
                time() - $start < $this->timeout
            ) {
                // Handle UDP responses
                if (! empty($sockets)) {
                    yield from $this->handleUdpResponses(
                        $sockets,
                        $queryMap,
                        $results,
                        $tcpFallbacks
                    );
                }

                // Handle TCP responses
                if (! empty($tcpSockets)) {
                    yield from $this->handleTcpResponses(
                        $tcpSockets,
                        $queryMap,
                        $results
                    );
                }

                // Process TCP fallbacks for truncated UDP responses
                foreach ($tcpFallbacks as $fallback) {
                    yield from $this->initializeTcpQuery(
                        $fallback,
                        $tcpSockets,
                        $queryMap
                    );
                }

                $tcpFallbacks = [];
                yield;
            }

            // Check for timeouts
            if (
                time() - $start >= $this->timeout &&
                (! empty($sockets) || ! empty($tcpSockets))
            ) {
                $timeoutQueries = [];
                foreach ($queryMap as $query) {
                    $timeoutQueries[] =
                        $query["hostname"]." (".$query["type"].")";
                }
                if (! empty($timeoutQueries)) {
                    throw new DNSTimeoutException(
                        "DNS queries timed out: ".
                        implode(", ", $timeoutQueries),
                        $this->timeout
                    );
                }
            }

            // Cleanup remaining sockets
            foreach (array_merge($sockets, $tcpSockets) as $socket) {
                if ($socket instanceof Socket) {
                    socket_close($socket);
                    unset($socket);
                }
                yield;
            }

            return $results;
        };

        return Future::new($fn());
    }

    /**
     * Initialize UDP socket and send DNS query
     *
     * @param array{hostname: string, type?: string, server?: string} $query Query configuration
     * @param array<string, Socket> &$sockets Reference to sockets array
     * @param array<string, array{hostname: string, type: string, id: int, server: string, protocol: string, query?: string}> &$queryMap Reference to query mapping array
     * @return Generator<mixed>
     */
    private function initUDPQuery(
        array $query,
        array &$sockets,
        array &$queryMap
    ): Generator {
        $hostname = $query["hostname"];
        $type = $query["type"] ?? "A";
        $server = $query["server"] ?? "8.8.8.8";

        $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        VOsaka::getLoop()->getGracefulShutdown()->addSocket($socket);

        if (! $socket) {
            throw new DNSNetworkException(
                "Failed to create UDP socket for $hostname"
            );
        }

        if (! socket_set_nonblock($socket)) {
            socket_close($socket);
            unset($socket);
            throw new DNSNetworkException(
                "Failed to set non-blocking mode for $hostname"
            );
        }

        socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, [
            "sec" => $this->timeout,
            "usec" => 0,
        ]);

        $queryId = rand(1, 65535);
        $DNSQuery = yield from $this->createDNSQuery(
            $hostname,
            $type,
            $queryId,
            false
        );

        $result = socket_sendto(
            $socket,
            $DNSQuery,
            strlen($DNSQuery),
            0,
            $server,
            53
        );

        if ($result === false) {
            $error = socket_last_error($socket);
            yield socket_close($socket);
            unset($socket);
            throw new DNSNetworkException(
                "Failed to send UDP query for $hostname: ".
                socket_strerror($error)
            );
        }

        $key = $hostname."_".$type."_".$queryId."_udp";
        $sockets[$key] = $socket;
        $queryMap[$key] = [
            "hostname" => $hostname,
            "type" => $type,
            "id" => $queryId,
            "server" => $server,
            "protocol" => "udp",
            "query" => $DNSQuery,
        ];

        // echo "Sent UDP query for $hostname ($type) to $server\n";
        yield;
    }

    /**
     * Initialize TCP socket and send DNS query
     *
     * @param array{hostname: string, type: string, id: int, server: string} $query Query configuration
     * @param array<string, Socket> &$tcpSockets Reference to TCP sockets array
     * @param array<string, array{hostname: string, type: string, id: int, server: string, protocol: string}> &$queryMap Reference to query mapping array
     * @return Generator<mixed>
     */
    private function initializeTcpQuery(
        array $query,
        array &$tcpSockets,
        array &$queryMap
    ): Generator {
        $hostname = $query["hostname"];
        $type = $query["type"];
        $server = $query["server"];
        $queryId = $query["id"];

        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        VOsaka::getLoop()->getGracefulShutdown()->addSocket($socket);

        if (! $socket) {
            throw new DNSNetworkException(
                "Failed to create TCP socket for $hostname"
            );
        }

        socket_set_nonblock($socket);
        socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, [
            "sec" => $this->timeout,
            "usec" => 0,
        ]);

        $result = @socket_connect($socket, $server, 53);
        if (
            $result !== false ||
            socket_last_error($socket) == SOCKET_EINPROGRESS
        ) {
            $DNSQuery = yield from $this->createDNSQuery(
                $hostname,
                $type,
                $queryId,
                true
            );
            $length = pack("n", strlen($DNSQuery));

            socket_write($socket, $length.$DNSQuery);

            $key = $hostname."_".$type."_".$queryId."_tcp";
            $tcpSockets[$key] = $socket;
            $queryMap[$key] = [
                "hostname" => $hostname,
                "type" => $type,
                "id" => $queryId,
                "server" => $server,
                "protocol" => "tcp",
            ];

            // echo "Sent TCP query for $hostname ($type) to $server\n";
        } else {
            $error = socket_last_error($socket);
            socket_close($socket);
            unset($socket);
            throw new DNSNetworkException(
                "Failed to connect TCP for $hostname: ".
                socket_strerror($error)
            );
        }
        yield;
    }

    /**
     * Handle UDP responses and process truncated responses
     *
     * @param array<string, Socket> &$sockets Reference to UDP sockets array
     * @param array<string, array{hostname: string, type: string, id: int, server: string, protocol: string, query?: string}> $queryMap Query mapping array
     * @param array<array{hostname: string, type: string, server: string, protocol: string, records: array, DNSsec?: array}> &$results Reference to results array
     * @param array<array{hostname: string, type: string, id: int, server: string}> &$tcpFallbacks Reference to TCP fallback array
     * @return Generator<mixed>
     */
    private function handleUdpResponses(
        array &$sockets,
        array $queryMap,
        array &$results,
        array &$tcpFallbacks
    ): Generator {
        $read = array_values($sockets);
        $write = null;
        $except = null;

        $selectResult = socket_select($read, $write, $except, 0, 100000); // 0.1 second timeout

        if ($selectResult > 0) {
            foreach ($read as $socket) {
                $response = "";
                $from = "";
                $port = 0;

                $recvResult = socket_recvfrom(
                    $socket,
                    $response,
                    $this->bufferSize,
                    0,
                    $from,
                    $port
                );

                if ($recvResult !== false && strlen($response) > 0) {
                    $key = array_search($socket, $sockets);
                    if ($key !== false) {
                        $queryInfo = $queryMap[$key];
                        // echo "Received UDP response for {$queryInfo["hostname"]} ({$queryInfo["type"]}), length: " .
                        //     strlen($response) .
                        //     "\n";

                        // Check if truncated (TC bit set)
                        if ($this->isTruncated($response)) {
                            // echo "Response truncated, falling back to TCP\n";
                            $tcpFallbacks[] = $queryInfo;
                        } else {
                            $records = $this->parseDNSResponse(
                                $response,
                                $queryInfo["id"],
                                $queryInfo["type"]
                            );

                            $result = [
                                "hostname" => $queryInfo["hostname"],
                                "type" => $queryInfo["type"],
                                "server" => $queryInfo["server"],
                                "protocol" => "udp",
                                "records" => $records,
                            ];

                            // Perform DNSSEC validation if enabled
                            if ($this->enableDNSsec) {
                                $result["DNSsec"] = $this->validateDNSsec(
                                    $records
                                );
                            }

                            $results[] = $result;
                        }

                        socket_close($socket);
                        unset($sockets[$key]);
                        unset($queryMap[$key]);
                    }
                }
                yield;
            }
        }
        yield;
    }

    /**
     * Handle TCP responses and parse DNS data
     *
     * @param array<string, Socket> &$tcpSockets Reference to TCP sockets array
     * @param array<string, array{hostname: string, type: string, id: int, server: string, protocol: string}> $queryMap Query mapping array
     * @param array<array{hostname: string, type: string, server: string, protocol: string, records: array, DNSsec?: array}> &$results Reference to results array
     * @return Generator<mixed>
     */
    private function handleTcpResponses(
        array &$tcpSockets,
        array $queryMap,
        array &$results
    ): Generator {
        $read = array_values($tcpSockets);
        $write = null;
        $except = null;

        if (socket_select($read, $write, $except, 0, 100000) > 0) {
            foreach ($read as $socket) {
                $lengthData = socket_read($socket, 2);
                if ($lengthData && strlen($lengthData) == 2) {
                    $length = unpack("n", $lengthData)[1];
                    $response = socket_read($socket, $length);

                    if ($response && strlen($response) == $length) {
                        $key = array_search($socket, $tcpSockets);
                        if ($key !== false) {
                            $queryInfo = $queryMap[$key];
                            // echo "Received TCP response for {$queryInfo["hostname"]} ({$queryInfo["type"]}), length: $length\n";

                            $records = $this->parseDNSResponse(
                                $response,
                                $queryInfo["id"],
                                $queryInfo["type"]
                            );

                            $result = [
                                "hostname" => $queryInfo["hostname"],
                                "type" => $queryInfo["type"],
                                "server" => $queryInfo["server"],
                                "protocol" => "tcp",
                                "records" => $records,
                            ];

                            if ($this->enableDNSsec) {
                                $result["DNSsec"] = $this->validateDNSsec(
                                    $records
                                );
                            }

                            $results[] = $result;

                            socket_close($socket);
                            unset($tcpSockets[$key]);
                            unset($queryMap[$key]);
                        }
                    }
                }
                yield;
            }
        }
        yield;
    }

    /**
     * Create DNS query packet in wire format
     *
     * @param string $hostname The hostname to query
     * @param string $type DNS record type (A, AAAA, MX, etc.)
     * @param int $queryId Unique query identifier
     * @param bool $isTcp Whether this is a TCP query
     * @return Generator<string> Binary DNS query packet
     */
    private function createDNSQuery(
        string $hostname,
        string $type,
        int $queryId,
        bool $isTcp = false
    ): Generator {
        $typeCode = DNSType::fromName($type)?->name ?? DNSType::A->name;
        $flags = 0x0100; // Standard query with recursion desired
        $header = pack(
            "nnnnnn",
            $queryId, // ID
            $flags, // Flags
            1, // Questions
            0, // Answer RRs
            0, // Authority RRs
            0 // Additional RRs
        );

        // Convert hostname to DNS format
        $question = "";
        if ($type === "PTR" && filter_var($hostname, FILTER_VALIDATE_IP)) {
            $hostname = $this->reverseIpForPtr($hostname);
        }

        $parts = explode(".", $hostname);
        foreach ($parts as $part) {
            $question .= chr(strlen($part)).$part;
            yield;
        }
        $question .= "\x00";

        // Query type and class
        $question .= pack("nn", $typeCode, 1); // Type and Class (IN)

        return $header.$question;
    }

    /**
     * Create EDNS(0) OPT record for extended DNS functionality
     *
     * @return string Binary EDNS OPT record
     */
    private function createEDNSRecord(): string
    {
        $name = "\x00"; // Root domain
        $type = pack("n", 41); // OPT type
        $udpSize = pack("n", $this->bufferSize); // UDP payload size
        $rcode = "\x00"; // Extended RCODE
        $version = "\x00"; // EDNS version
        $flags = pack("n", $this->enableDNSsec ? 0x8000 : 0x0000); // DO bit
        $rdlen = pack("n", 0); // No additional data

        return $name.$type.$udpSize.$rcode.$version.$flags.$rdlen;
    }

    /**
     * Convert IP address to reverse DNS format for PTR queries
     *
     * @param string $ip IP address (IPv4 or IPv6)
     * @return string Reversed IP format for PTR queries
     */
    private function reverseIpForPtr(string $ip): string
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $parts = explode(".", $ip);
            return implode(".", array_reverse($parts)).".in-addr.arpa";
        } elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $addr = inet_pton($ip);
            $chars = str_split(bin2hex($addr));
            return implode(".", array_reverse($chars)).".ip6.arpa";
        }
        return $ip;
    }

    /**
     * Check if DNS response is truncated (TC bit set)
     *
     * @param string $response Binary DNS response
     * @return bool True if response is truncated
     */
    private function isTruncated(string $response): bool
    {
        if (strlen($response) < 3) {
            return false;
        }
        $flags = unpack("n", substr($response, 2, 2))[1];
        return ($flags & 0x0200) != 0; // TC bit
    }

    /**
     * Parse DNS response and extract records
     *
     * @param string $response Binary DNS response
     * @param int $expectedId Expected query ID
     * @param string $queryType Query type for validation
     * @return array<array{name: string, type: string, class: int, ttl: int, data: mixed, raw_type: int, section: string}> Parsed DNS records
     */
    private function parseDNSResponse(
        string $response,
        int $expectedId,
        string $queryType
    ): array {
        if (strlen($response) < 12) {
            throw new DNSParseException(
                "DNS response too short: ".
                strlen($response).
                " bytes (minimum 12 bytes required)",
                $response,
                0
            );
        }

        $header = unpack(
            "nid/nflags/nqdcount/nancount/nnscount/narcount",
            substr($response, 0, 12)
        );

        // echo "Response ID: {$header["id"]}, Expected: $expectedId\n";
        // echo "Flags: 0x" . dechex($header["flags"]) . "\n";
        // echo "Questions: {$header["qdcount"]}, Answers: {$header["ancount"]}\n";

        if ($header["id"] != $expectedId) {
            throw new DNSQueryException(
                "DNS response ID mismatch: expected {$expectedId}, got {$header["id"]}"
            );
        }

        // Check response code
        $rcode = $header["flags"] & 0x000f;
        if ($rcode != 0) {
            $query = ["hostname" => "unknown", "type" => $queryType];
            throw new DNSException(
                "DNS query failed with error code: $rcode",
                0,
                null,
                $query,
                $response,
                $rcode
            );
        }

        $offset = 12;
        $records = [];

        // Skip question section
        for ($i = 0; $i < $header["qdcount"]; $i++) {
            $offset = $this->skipDNSName($response, $offset);
            $offset += 4;
        }

        // Parse answer section
        for ($i = 0; $i < $header["ancount"]; $i++) {
            try {
                $record = $this->parseRecord($response, $offset);
                if ($record) {
                    $record->section = "answer";
                    $records[] = $record;
                    $offset = $record->nextOffset;
                } else {
                    break;
                }
            } catch (DNSParseException $e) {
                // Log parse error but continue with other records
                break;
            }
        }

        // Parse authority section
        for ($i = 0; $i < $header["nscount"]; $i++) {
            try {
                $record = $this->parseRecord($response, $offset);
                if ($record) {
                    $record->section = "authority";
                    $records[] = $record;
                    $offset = $record->nextOffset;
                } else {
                    break;
                }
            } catch (DNSParseException $e) {
                // Log parse error but continue with other records
                break;
            }
        }

        // Parse additional section
        for ($i = 0; $i < $header["arcount"]; $i++) {
            try {
                $record = $this->parseRecord($response, $offset);
                if ($record) {
                    $record->section = "additional";
                    $records[] = $record;
                    $offset = $record->nextOffset;
                } else {
                    break;
                }
            } catch (DNSParseException $e) {
                // Log parse error but continue with other records
                break;
            }
        }

        // echo "Parsed " . count($records) . " records\n";
        return $records;
    }

    /**
     * Parse individual DNS record from response
     *
     * @param string $response Binary DNS response
     * @param int $offset Current offset in response
     * @return Record|null
     */
    private function parseRecord(string $response, int $offset): ?Record
    {
        if ($offset >= strlen($response)) {
            throw new DNSParseException(
                "Cannot parse record: offset beyond response data",
                $response,
                $offset
            );
        }

        $name = $this->readDNSName($response, $offset);
        $offset = $this->skipDNSName($response, $offset);

        if ($offset + 10 > strlen($response)) {
            throw new DNSParseException(
                "Cannot parse record header: insufficient data for record at offset $offset",
                $response,
                $offset
            );
        }

        $record = unpack(
            "ntype/nclass/Nttl/ndlen",
            substr($response, $offset, 10)
        );
        $offset += 10;

        if ($offset + $record["dlen"] > strlen($response)) {
            throw new DNSParseException(
                "Cannot parse record data: record length {$record["dlen"]} exceeds remaining response data",
                $response,
                $offset
            );
        }

        $parsedData = $this->parseRecordData(
            $response,
            $offset,
            $record["type"],
            $record["dlen"]
        );

        // Use the structured object directly as data
        $data = $parsedData;

        return new Record(
            $name,
            array_search($record["type"], DNSType::toArray()) ?:
            (string) $record["type"],
            $record["class"],
            $record["ttl"],
            $data,
            $record["type"],
            $offset + $record["dlen"]
        );
    }

    /**
     * Parse record data based on DNS record type
     *
     * @param string $response DNS response data
     * @param int $offset Current offset in response
     * @param int $type DNS record type
     * @param int $length Data length
     * @return AddressRecord|MxRecord|TxtRecord|SrvRecord|SoaRecord|NameRecord|RawRecord Structured parsed record data object
     */
    private function parseRecordData(
        string $response,
        int $offset,
        int $type,
        int $length
    ): AddressRecord|MxRecord|TxtRecord|SrvRecord|SoaRecord|NameRecord|RawRecord {
        if ($offset + $length > strlen($response)) {
            throw new DNSParseException(
                "Cannot parse record data: data length $length exceeds remaining response data",
                $response,
                $offset
            );
        }

        switch ($type) {
            case 1: // A record
                if ($length == 4) {
                    return new AddressRecord(
                        true,
                        null,
                        inet_ntop(substr($response, $offset, 4))
                    );
                }
                return new AddressRecord(false, "Invalid A record", "");

            case 28: // AAAA record
                if ($length == 16) {
                    return new AddressRecord(
                        true,
                        null,
                        inet_ntop(substr($response, $offset, 16))
                    );
                }
                return new AddressRecord(false, "Invalid AAAA record", "");

            case 5: // CNAME
            case 2: // NS
            case 12: // PTR
                return new NameRecord(
                    true,
                    null,
                    $this->readDNSName($response, $offset)
                );

            case 15: // MX record
                if ($length < 3) {
                    return new MxRecord(false, "Invalid MX record", 0, "");
                }
                $preference = unpack("n", substr($response, $offset, 2))[1];
                $exchange = $this->readDNSName($response, $offset + 2);
                return new MxRecord(true, null, $preference, $exchange);

            case 16: // TXT record
                return $this->parseTxtRecord($response, $offset, $length);

            case 33: // SRV record
                return $this->parseSrvRecord($response, $offset);

            case 6: // SOA record
                return $this->parseSoaRecord($response, $offset);

            default:
                return new RawRecord(
                    true,
                    null,
                    bin2hex(substr($response, $offset, $length))
                );
        }
    }

    /**
     * Parse TXT record data
     *
     * @param string $response DNS response data
     * @param int $offset Current offset in response
     * @param int $length Data length
     * @return TxtRecord Structured TXT record data object
     */
    private function parseTxtRecord(
        string $response,
        int $offset,
        int $length
    ): TxtRecord {
        $texts = [];
        $pos = $offset;
        while ($pos < $offset + $length) {
            $len = ord($response[$pos]);
            $pos++;
            if ($len > 0 && $pos + $len <= $offset + $length) {
                $texts[] = substr($response, $pos, $len);
                $pos += $len;
            } else {
                break;
            }
        }
        return new TxtRecord(true, null, implode("", $texts), $texts);
    }

    /**
     * Parse SRV record data
     *
     * @param string $response DNS response data
     * @param int $offset Current offset in response
     * @return SrvRecord Structured SRV record data object
     */
    private function parseSrvRecord(string $response, int $offset): SrvRecord
    {
        if (strlen($response) < $offset + 6) {
            return new SrvRecord(false, "Invalid SRV record", 0, 0, 0, "");
        }

        $srv = unpack("npriority/nweight/nport", substr($response, $offset, 6));
        $target = $this->readDNSName($response, $offset + 6);
        return new SrvRecord(
            true,
            null,
            $srv["priority"],
            $srv["weight"],
            $srv["port"],
            $target
        );
    }

    /**
     * Parse SOA record data
     *
     * @param string $response DNS response data
     * @param int $offset Current offset in response
     * @return SoaRecord Structured SOA record data object
     */
    private function parseSoaRecord(string $response, int $offset): SoaRecord
    {
        $primary = $this->readDNSName($response, $offset);
        $offset2 = $this->skipDNSName($response, $offset);
        $admin = $this->readDNSName($response, $offset2);
        $offset3 = $this->skipDNSName($response, $offset2);

        if (strlen($response) < $offset3 + 20) {
            return new SoaRecord(
                false,
                "Invalid SOA record",
                "",
                "",
                0,
                0,
                0,
                0,
                0
            );
        }

        $soa = unpack(
            "Nserial/Nrefresh/Nretry/Nexpire/Nminimum",
            substr($response, $offset3, 20)
        );

        return new SoaRecord(
            true,
            null,
            $primary,
            $admin,
            $soa["serial"],
            $soa["refresh"],
            $soa["retry"],
            $soa["expire"],
            $soa["minimum"]
        );
    }

    /**
     * Validate DNSSEC signatures and keys
     *
     * @param array<array{name: string, type: string, class: int, ttl: int, data: mixed, raw_type: int, section: string}> $records DNS records to validate
     * @return array{status: string, signatures: array, keys: array, ds_records: array} DNSSEC validation results
     */
    private function validateDNSsec(array $records): array
    {
        if (! $this->enableDNSsec) {
            return [
                "status" => "disabled",
                "signatures" => [],
                "keys" => [],
                "ds_records" => [],
            ];
        }

        // Check for DNSSEC records
        $rrsigRecords = array_filter(
            $records,
            fn (Record $record) => $record->type === "RRSIG"
        );
        $DNSkeyRecords = array_filter(
            $records,
            fn (Record $record) => $record->type === "DNSKEY"
        );
        $dsRecords = array_filter(
            $records,
            fn (Record $record) => $record->type === "DS"
        );

        if (empty($rrsigRecords) && $this->enableDNSsec) {
            throw new DNSSECException(
                "DNSSEC validation enabled but no RRSIG records found",
                "missing_signatures",
                $records
            );
        }

        // Simplified DNSSEC validation for now
        return [
            "status" => "unverified",
            "signatures" => $rrsigRecords,
            "keys" => $DNSkeyRecords,
            "ds_records" => $dsRecords,
        ];
    }

    /**
     * Read DNS name from response with compression support
     *
     * @param string $response Binary DNS response
     * @param int $offset Current offset in response
     * @return string Parsed DNS name
     */
    private function readDNSName(string $response, int $offset): string
    {
        $name = "";
        $jumped = false;
        $originalOffset = $offset;
        $maxJumps = 10;
        $jumps = 0;

        while ($offset < strlen($response) && $jumps < $maxJumps) {
            $len = ord($response[$offset]);

            if ($len == 0) {
                if (! $jumped) {
                    $originalOffset = $offset + 1;
                }
                break;
            }

            if (($len & 0xc0) == 0xc0) {
                if (! $jumped) {
                    $originalOffset = $offset + 2;
                }
                $offset = (($len & 0x3f) << 8) | ord($response[$offset + 1]);
                $jumped = true;
                $jumps++;
                continue;
            }

            $offset++;
            if ($offset + $len > strlen($response)) {
                break;
            }

            if ($name) {
                $name .= ".";
            }
            $name .= substr($response, $offset, $len);
            $offset += $len;
        }

        return $name;
    }

    /**
     * Skip DNS name in response and return next offset
     *
     * @param string $response Binary DNS response
     * @param int $offset Current offset in response
     * @return int Next offset after DNS name
     */
    private function skipDNSName(string $response, int $offset): int
    {
        while ($offset < strlen($response)) {
            $len = ord($response[$offset]);
            if ($len == 0) {
                return $offset + 1;
            }
            if (($len & 0xc0) == 0xc0) {
                return $offset + 2;
            }
            $offset += $len + 1;
        }
        return $offset;
    }
}
