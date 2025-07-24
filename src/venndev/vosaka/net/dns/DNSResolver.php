<?php

declare(strict_types=1);

namespace venndev\vosaka\net\dns;

use Generator;
use Exception;
use venndev\vosaka\core\Future;
use venndev\vosaka\core\Result;
use venndev\vosaka\net\exceptions\NetworkException;
use venndev\vosaka\net\tcp\TCPAddress;
use venndev\vosaka\net\udp\UDP;

/**
 * DNS Resolver
 */
class DNSResolver
{
    private array $nameservers;
    private float $timeout;
    private array $cache = [];
    private int $cacheSize = 1000;

    public function __construct(
        array $nameservers = ['8.8.8.8:53', '8.8.4.4:53'],
        float $timeout = 5.0
    ) {
        $this->nameservers = $nameservers;
        $this->timeout = $timeout;
    }

    /**
     * Resolve domain name
     */
    public function resolve(string $domain, RecordType $type = RecordType::A): Result
    {
        return Future::new($this->doResolve($domain, $type));
    }

    private function doResolve(string $domain, RecordType $type): Generator
    {
        // Check cache
        $cacheKey = "{$domain}:{$type->value}";
        if (isset($this->cache[$cacheKey])) {
            $cached = $this->cache[$cacheKey];
            if ($cached['expires'] > time()) {
                return $cached['records'];
            }
            unset($this->cache[$cacheKey]);
        }

        // Build query
        $query = new DNSQuery($domain);
        $query->setType($type);
        $packet = $query->build();

        // Try each nameserver
        $lastError = null;
        foreach ($this->nameservers as $nameserver) {
            try {
                $response = yield from $this->queryNameserver($nameserver, $packet, $query->getId());

                // Cache results
                $records = $response->getAnswers();
                if (!empty($records)) {
                    $minTTL = min(array_map(fn($r) => $r->ttl, $records));
                    $this->addToCache($cacheKey, $records, $minTTL);
                }

                return $records;
            } catch (\Exception $e) {
                $lastError = $e;
                continue;
            }
        }

        throw new NetworkException(
            "Failed to resolve {$domain}: " . ($lastError ? $lastError->getMessage() : 'Unknown error')
        );
    }

    /**
     * Query specific nameserver
     */
    private function queryNameserver(string $nameserver, string $packet, int $queryId): Generator
    {
        $socket = yield UDP::socket();
        $nsAddress = TCPAddress::parse($nameserver);

        try {
            // Send query
            yield $socket->sendTo($packet, $nsAddress);

            // Wait for response
            $start = microtime(true);
            while ((microtime(true) - $start) < $this->timeout) {
                $result = yield $socket->receiveFrom();
                $response = new DNSResponse($result['data']);

                // Verify response ID matches query
                if ($response->getId() === $queryId) {
                    if ($response->getResponseCode() !== ResponseCode::NO_ERROR) {
                        throw new NetworkException(
                            "DNS error: " . $response->getResponseCode()->name
                        );
                    }

                    return $response;
                }
            }

            throw new NetworkException("DNS query timeout");
        } finally {
            $socket->close();
        }
    }

    /**
     * Add to cache
     */
    private function addToCache(string $key, array $records, int $ttl): void
    {
        // Limit cache size
        if (count($this->cache) >= $this->cacheSize) {
            array_shift($this->cache);
        }

        $this->cache[$key] = [
            'records' => $records,
            'expires' => time() + $ttl
        ];
    }

    /**
     * Clear cache
     */
    public function clearCache(): void
    {
        $this->cache = [];
    }

    /**
     * Resolve A records (IPv4)
     */
    public function resolveA(string $domain): Result
    {
        return $this->resolve($domain, RecordType::A);
    }

    /**
     * Resolve AAAA records (IPv6)
     */
    public function resolveAAAA(string $domain): Result
    {
        return $this->resolve($domain, RecordType::AAAA);
    }

    /**
     * Resolve MX records
     */
    public function resolveMX(string $domain): Result
    {
        return $this->resolve($domain, RecordType::MX);
    }

    /**
     * Resolve TXT records
     */
    public function resolveTXT(string $domain): Result
    {
        return $this->resolve($domain, RecordType::TXT);
    }

    /**
     * Resolve all record types
     */
    public function resolveAll(string $domain): Result
    {
        return Future::new($this->doResolveAll($domain));
    }

    private function doResolveAll(string $domain): Generator
    {
        $types = [
            RecordType::A,
            RecordType::AAAA,
            RecordType::MX,
            RecordType::TXT,
            RecordType::CNAME
        ];

        $results = [];
        foreach ($types as $type) {
            try {
                $records = yield $this->resolve($domain, $type);
                if (!empty($records)) {
                    $results[$type->name] = $records;
                }
            } catch (Exception) {
                // Ignore errors for individual record types
                // Log or handle as needed
                continue;
            }
        }

        return $results;
    }
}
