<?php

declare(strict_types=1);

namespace venndev\vosaka\io;

use Error;
use Generator;
use RuntimeException;
use venndev\vosaka\utils\Result;

final class JoinHandle
{
    public mixed $result = null;
    public bool $done = false;
    public bool $justSpawned = true;
    private static array $instances = [];

    private function __construct(public int $id)
    {
        // Private constructor to prevent direct instantiation
    }

    public static function c(int $id): Result
    {
        if (isset(self::$instances[$id])) {
            throw new RuntimeException("Wait instance with ID {$id} already exists.");
        }

        self::$instances[$id] = new self($id);
        return new Result(self::tryingDone($id));
    }

    public static function done(int $id, mixed $result): void
    {
        $instance = self::getInstance($id);
        $instance->result = $result;
        $instance->done = true;

        if ($instance->justSpawned) {
            if ($result instanceof Throwable || $result instanceof Error) {
                throw $result;
            }

            unset(self::$instances[$id]); // Clean up the instance if it was just spawned
        }
    }

    public static function isDone(int $id): bool
    {
        return isset(self::$instances[$id]) && self::$instances[$id]->done;
    }

    private static function getInstance(int $id): self
    {
        if (!isset(self::$instances[$id])) {
            throw new RuntimeException("Wait instance with ID {$id} does not exist.");
        }
        return self::$instances[$id];
    }

    private static function tryingDone(int $id): Generator
    {
        $instance = self::getInstance($id);
        $instance->justSpawned = false;

        while (self::getInstance($id)->done === false) {
            yield; // Yield control to the event loop until done
        }

        $instance = self::getInstance($id);
        $result = $instance->result;
        unset(self::$instances[$id]); // Clean up the instance after use
        return $result;
    }
}