<?php

declare(strict_types=1);

require_once "../vendor/autoload.php";

use venndev\vosaka\net\dns\DNSClient;
use venndev\vosaka\net\dns\model\Record;
use venndev\vosaka\VOsaka;

/**
 * Simple DNS Test - Basic functionality test
 */
function simpleDnsTest(): Generator
{
    echo "=== Simple DNS Test ===\n";

    try {
        $client = new DNSClient(timeout: 10);

        // Test basic A record query
        echo "Testing A record for google.com...\n";
        $queries = [["hostname" => "google.com", "type" => "A"]];
        $results = yield from $client->asyncDnsQuery($queries)->unwrap();

        if (!empty($results)) {
            $result = $results[0];
            echo "Query successful!\n";
            echo "Hostname: {$result["hostname"]}\n";
            echo "Type: {$result["type"]}\n";
            echo "Server: {$result["server"]}\n";
            echo "Protocol: {$result["protocol"]}\n";
            echo "Records found: " . count($result["records"]) . "\n";

            foreach ($result["records"] as $record) {
                echo "  Record: {$record->name} -> ";
                echo "Type: {$record->type}, ";

                if (is_object($record->data)) {
                    echo "Data: " . get_class($record->data) . " object";
                } else {
                    echo "Data: {$record->data}";
                }
                echo ", TTL: {$record->ttl}\n";
            }
        } else {
            echo "No results returned\n";
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        echo "This might be due to network connectivity or DNS server issues.\n";
    }

    echo "\n";
}

/**
 * Test multiple queries
 */
function multipleQueriesTest(): Generator
{
    echo "=== Multiple Queries Test ===\n";

    try {
        $client = new DNSClient(timeout: 10);

        $queries = [
            ["hostname" => "google.com", "type" => "A"],
            ["hostname" => "cloudflare.com", "type" => "A"],
        ];

        echo "Testing multiple A record queries...\n";
        $results = yield from $client->asyncDnsQuery($queries)->unwrap();

        echo "Results received: " . count($results) . "\n";

        foreach ($results as $result) {
            echo "\nResult for {$result["hostname"]}:\n";
            echo "  Records: " . count($result["records"]) . "\n";

            foreach ($result["records"] as $record) {
                if (is_object($record->data)) {
                    echo "  {$record->name} -> " .
                        get_class($record->data) .
                        "\n";
                } else {
                    echo "  {$record->name} -> {$record->data}\n";
                }
            }
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }

    echo "\n";
}

/**
 * Test different record types
 */
function recordTypesTest(): Generator
{
    echo "=== Record Types Test ===\n";

    try {
        $client = new DNSClient(timeout: 15);

        $domain = "google.com";
        $types = ["A", "AAAA", "MX", "TXT", "NS"];

        foreach ($types as $type) {
            echo "Testing {$type} record for {$domain}...\n";

            try {
                $queries = [["hostname" => $domain, "type" => $type]];
                $results = yield from $client
                    ->asyncDnsQuery($queries)
                    ->unwrap();

                if (!empty($results)) {
                    $result = $results[0];
                    echo "  Success! Found " .
                        count($result["records"]) .
                        " records\n";

                    foreach ($result["records"] as $record) {
                        echo "    {$record->name} ({$record->type}) -> ";
                        if (is_object($record->data)) {
                            echo get_class($record->data);
                        } else {
                            echo $record->data;
                        }
                        echo "\n";
                    }
                } else {
                    echo "  No records found\n";
                }
            } catch (Exception $e) {
                echo "  Error: " . $e->getMessage() . "\n";
            }

            echo "\n";
        }
    } catch (Exception $e) {
        echo "General error: " . $e->getMessage() . "\n";
    }
}

/**
 * Main test runner
 */
function runTests(): Generator
{
    echo "VOsaka Simple DNS Client Test\n";
    echo "============================\n\n";

    yield from simpleDnsTest();
    yield from multipleQueriesTest();
    yield from recordTypesTest();

    echo "Tests completed!\n";
}

// Execute tests
if (php_sapi_name() === "cli") {
    VOsaka::spawn(runTests());
    VOsaka::run();
}
