<?php

declare(strict_types=1);

namespace venndev\vosaka\io;

use Error;
use Generator;
use RuntimeException;
use Throwable;
use venndev\vosaka\utils\Result;

final class JoinHandle
{
    public mixed $result = null;
    public bool $done = false;
    public bool $justSpawned = true;

    /** @var array<int, self> */
    private static array $instances = [];

    private function __construct(public int $id)
    {
        // Private constructor to prevent direct instantiation
    }

    public static function c(int $id): Result
    {
        if (isset(self::$instances[$id])) {
            throw new RuntimeException("JoinHandle with ID {$id} already exists.");
        }

        $handle = new self($id);
        self::$instances[$id] = $handle;

        return new Result(self::tryingDone($handle));
    }

    public static function done(int $id, mixed $result): void
    {
        $handle = self::getInstance($id);
        $handle->result = $result;
        $handle->done = true;

        if ($handle->justSpawned) {
            if ($result instanceof Throwable || $result instanceof Error) {
                unset(self::$instances[$id]); // clean up first
                throw $result;
            }

            unset(self::$instances[$id]); // cleanup
        }
    }

    public static function isDone(int $id): bool
    {
        $handle = self::$instances[$id] ?? null;
        return $handle?->done ?? false;
    }

    private static function getInstance(int $id): self
    {
        return self::$instances[$id]
            ?? throw new RuntimeException("JoinHandle with ID {$id} does not exist.");
    }

    private static function tryingDone(self $handle): Generator
    {
        $handle->justSpawned = false;

        while (!$handle->done) {
            yield;
        }

        $result = $handle->result;
        unset(self::$instances[$handle->id]); // cleanup after finished

        return $result;
    }
}
