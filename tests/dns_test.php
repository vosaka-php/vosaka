<?php

declare(strict_types=1);

require_once "../vendor/autoload.php";

use venndev\vosaka\net\DNS\DNSClient;
use venndev\vosaka\net\DNS\DNSType;
use venndev\vosaka\net\DNS\DNSSecAlgorithm;
use venndev\vosaka\net\DNS\model\Record;
use venndev\vosaka\VOsaka;

/**
 * Example 1: Basic A Record Query
 */
function basicARecordQuery(): Generator
{
    echo "=== Example 1: Basic A Record Query ===\n";

    try {
        $client = new DNSClient();

        $queries = [["hostname" => "google.com", "type" => "A"]];

        $results = yield from $client->asyncDNSQuery($queries)->unwrap();

        foreach ($results as $result) {
            echo "Querying: {$result["hostname"]} ({$result["type"]})\n";
            echo "Server: {$result["server"]}, Protocol: {$result["protocol"]}\n";

            /**
             * @var Record $record
             */
            foreach ($result["records"] as $record) {
                if ($record->type === "A") {
                    echo "  IPv4 Address: {$record->data} (TTL: {$record->ttl})\n";
                }
            }
        }
    } catch (Exception $e) {
        echo "Failed to perform basic A record query: " .
            $e->getMessage() .
            "\n";
    }
    echo "\n";
}

/**
 * Example 2: Multiple Record Types for Same Domain
 */
function multipleRecordTypes(): Generator
{
    echo "=== Example 2: Multiple Record Types ===\n";

    try {
        $client = new DNSClient(timeout: 15);

        $domain = "google.com";
        $queries = [
            ["hostname" => $domain, "type" => "A"],
            ["hostname" => $domain, "type" => "AAAA"],
            ["hostname" => $domain, "type" => "MX"],
            ["hostname" => $domain, "type" => "TXT"],
            ["hostname" => $domain, "type" => "NS"],
        ];

        $results = yield from $client->asyncDNSQuery($queries)->unwrap();

        $recordsByType = [];
        foreach ($results as $result) {
            $recordsByType[$result["type"]] = $result["records"];
        }

        foreach ($recordsByType as $type => $records) {
            echo "\n{$type} Records for {$domain}:\n";
            foreach ($records as $record) {
                echo "  {$record->name} -> ";
                if (is_object($record->data)) {
                    if (
                        $type === "MX" &&
                        method_exists($record->data, "preference") &&
                        method_exists($record->data, "exchange")
                    ) {
                        echo "Priority: {$record->data->preference}, Exchange: {$record->data->exchange}";
                    } elseif (
                        $type === "SRV" &&
                        method_exists($record->data, "priority") &&
                        method_exists($record->data, "weight")
                    ) {
                        echo "Priority: {$record->data->priority}, Weight: {$record->data->weight}, Port: {$record->data->port}, Target: {$record->data->target}";
                    } elseif (
                        $type === "SOA" &&
                        method_exists($record->data, "primary")
                    ) {
                        echo "Primary: {$record->data->primary}, Admin: {$record->data->admin}, Serial: {$record->data->serial}";
                    } else {
                        echo get_class($record->data) .
                            ": " .
                            json_encode($record->data);
                    }
                } else {
                    echo $record->data;
                }
                echo " (TTL: {$record->ttl})\n";
            }
        }
    } catch (Exception $e) {
        echo "Failed to perform multiple record types query: " .
            $e->getMessage() .
            "\n";
    }
    echo "\n";
}

/**
 * Example 3: Using Different DNS Servers
 */
function differentDNSServers(): Generator
{
    echo "=== Example 3: Different DNS Servers ===\n";

    try {
        $client = new DNSClient();

        $hostname = "cloudflare.com";
        $queries = [
            ["hostname" => $hostname, "type" => "A", "server" => "8.8.8.8"], // Google DNS
            ["hostname" => $hostname, "type" => "A", "server" => "1.1.1.1"], // Cloudflare DNS
            [
                "hostname" => $hostname,
                "type" => "A",
                "server" => "208.67.222.222",
            ], // OpenDNS
            ["hostname" => $hostname, "type" => "A", "server" => "9.9.9.9"], // Quad9
        ];

        $results = yield from $client->asyncDNSQuery($queries)->unwrap();

        echo "Comparing results from different DNS servers for {$hostname}:\n";
        foreach ($results as $result) {
            echo "\nServer: {$result["server"]}\n";
            foreach ($result["records"] as $record) {
                if ($record->type === "A") {
                    echo "  {$record->data}\n";
                }
            }
        }
    } catch (Exception $e) {
        echo "Failed to query different DNS servers: " .
            $e->getMessage() .
            "\n";
    }
    echo "\n";
}

/**
 * Example 4: Reverse DNS Lookup
 */
function reverseDNSLookup(): Generator
{
    echo "=== Example 4: Reverse DNS Lookup ===\n";

    try {
        $client = new DNSClient();

        $ipAddresses = [
            "8.8.8.8", // Google DNS IPv4
            "1.1.1.1", // Cloudflare DNS IPv4
        ];

        $queries = [];
        foreach ($ipAddresses as $ip) {
            $queries[] = ["hostname" => $ip, "type" => "PTR"];
        }

        $results = yield from $client->asyncDNSQuery($queries)->unwrap();

        foreach ($results as $result) {
            echo "Reverse DNS for {$result["hostname"]}:\n";
            foreach ($result["records"] as $record) {
                if ($record->type === "PTR") {
                    echo "  -> {$record->data}\n";
                }
            }
        }
    } catch (Exception $e) {
        echo "Failed to perform reverse DNS lookup: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

/**
 * Example 5: DNSSEC-Enabled Queries
 */
function DNSsecQueries(): Generator
{
    echo "=== Example 5: DNSSEC-Enabled Queries ===\n";

    try {
        $client = new DNSClient(
            timeout: 30,
            enableDNSsec: true,
            enableEDNS: true,
            bufferSize: 8192
        );

        // Query a domain known to have DNSSEC
        $domain = "cloudflare.com";
        $queries = [["hostname" => $domain, "type" => "A"]];

        $results = yield from $client->asyncDNSQuery($queries)->unwrap();

        foreach ($results as $result) {
            echo "\n{$result["type"]} records for {$result["hostname"]}:\n";

            if (isset($result["DNSsec"])) {
                echo "DNSSEC Status: {$result["DNSsec"]["status"]}\n";
            }

            foreach ($result["records"] as $record) {
                echo "  {$record->name} ({$record->type}) -> ";
                if (is_object($record->data)) {
                    echo get_class($record->data) . " record";
                } else {
                    echo $record->data;
                }
                echo "\n";
            }
        }
    } catch (Exception $e) {
        echo "Failed to perform DNSSEC queries: " . $e->getMessage() . "\n";
        echo "DNSSEC queries may not be supported by all DNS servers.\n";
    }
    echo "\n";
}

/**
 * Example 6: Working with DNS Types Enum
 */
function DNSTypesDemo(): void
{
    echo "=== Example 6: DNS Types Enumeration ===\n";

    // Create DNS types from names
    $aType = DNSType::fromName("A");
    $mxType = DNSType::fromName("MX");

    echo "A record type code: " . $aType->value . "\n";
    echo "MX record type code: " . $mxType->value . "\n";

    // Get all DNS types
    $allTypes = DNSType::toArray();
    echo "\nAll supported DNS record types:\n";
    foreach ($allTypes as $name => $code) {
        $type = DNSType::fromValue($code);
        echo "  {$name} ({$code}): {$type->getDescription()}\n";
    }

    // Check for query-only types
    echo "\nQuery-only types:\n";
    foreach (DNSType::cases() as $type) {
        if ($type->isQueryOnly()) {
            echo "  {$type->name}: {$type->getDescription()}\n";
        }
    }

    // Check for DNSSEC types
    echo "\nDNSSEC-related types:\n";
    foreach (DNSType::cases() as $type) {
        if ($type->isDNSsecType()) {
            echo "  {$type->name}: {$type->getDescription()}\n";
        }
    }
    echo "\n";
}

/**
 * Example 7: DNSSEC Algorithms Information
 */
function DNSsecAlgorithmsDemo(): void
{
    echo "=== Example 7: DNSSEC Algorithms ===\n";

    // Get recommended algorithms
    $recommended = DNSSecAlgorithm::getRecommended();
    echo "Recommended DNSSEC algorithms:\n";
    foreach ($recommended as $algo) {
        echo "  {$algo->name} ({$algo->value}): {$algo->getDescription()}\n";
        echo "    Family: {$algo->getFamily()}, Hash: {$algo->getHashAlgorithm()}, Key Size: {$algo->getTypicalKeySize()} bits\n";
    }

    // Get deprecated algorithms
    $deprecated = DNSSecAlgorithm::getDeprecated();
    echo "\nDeprecated DNSSEC algorithms (avoid these):\n";
    foreach ($deprecated as $algo) {
        echo "  {$algo->name} ({$algo->value}): {$algo->getDescription()}\n";
    }

    // Check algorithm security status
    echo "\nAlgorithm security status:\n";
    foreach (DNSSecAlgorithm::cases() as $algo) {
        $status = $algo->isRecommended()
            ? "RECOMMENDED"
            : ($algo->isDeprecated()
                ? "DEPRECATED"
                : ($algo->isSecure()
                    ? "SECURE"
                    : "INSECURE"));
        echo "  {$algo->name}: {$status}\n";
    }
    echo "\n";
}

/**
 * Example 8: Error Handling and Edge Cases
 */
function errorHandlingDemo(): Generator
{
    echo "=== Example 8: Error Handling ===\n";

    try {
        $client = new DNSClient(timeout: 5); // Short timeout for demo

        $queries = [
            ["hostname" => "google.com", "type" => "A"], // Valid query for comparison
        ];

        $results = yield from $client->asyncDNSQuery($queries)->unwrap();

        echo "Queries attempted: " . count($queries) . "\n";
        echo "Results received: " . count($results) . "\n";

        foreach ($results as $result) {
            echo "\nQuery: {$result["hostname"]} ({$result["type"]}) via {$result["server"]}\n";
            echo "Records found: " . count($result["records"]) . "\n";
            if (empty($result["records"])) {
                echo "  (No records returned - may indicate error or NXDOMAIN)\n";
            } else {
                foreach ($result["records"] as $record) {
                    if (is_object($record->data)) {
                        echo "  {$record->name} -> " .
                            get_class($record->data) .
                            " record\n";
                    } else {
                        echo "  {$record->name} -> {$record->data}\n";
                    }
                }
            }
        }
    } catch (Exception $e) {
        echo "Expected error handling demo: " . $e->getMessage() . "\n";
        echo "This demonstrates how the DNS client handles various error conditions.\n";
    }
    echo "\n";
}

/**
 * Main execution function
 */
function main(): Generator
{
    echo "VOsaka DNS Client Examples\n";
    echo "=========================\n\n";

    try {
        // Run all examples with error handling
        yield from basicARecordQuery();
        yield from multipleRecordTypes();
        yield from differentDNSServers();
        yield from reverseDNSLookup();
        yield from DNSsecQueries();

        // These don't need generators
        DNSTypesDemo();
        DNSsecAlgorithmsDemo();

        yield from errorHandlingDemo();

        echo "All examples completed!\n";
    } catch (Exception $e) {
        echo "Error during DNS test execution: " . $e->getMessage() . "\n";
        echo "This might be due to network connectivity issues or DNS server problems.\n";
    }
}

// Execute examples
if (php_sapi_name() === "cli") {
    VOsaka::spawn(main());
    VOsaka::run();
}
