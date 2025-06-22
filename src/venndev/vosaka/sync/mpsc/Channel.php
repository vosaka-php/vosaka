<?php

declare(strict_types=1);

namespace venndev\vosaka\sync;

use Generator;
use RuntimeException;
use venndev\vosaka\utils\Result;

final class Channel
{
    private static array $channels = [];
    private int $id;
    private static int $nextId = 0;

    public function __construct(private ?int $capacity = null)
    {
        $this->id = self::$nextId++;
        self::$channels[$this->id] = [];
    }

    public function send(mixed $data): Result
    {
        $fn = function () use ($data): Generator {
            if (!isset(self::$channels[$this->id])) {
                throw new RuntimeException("Channel {$this->id} does not exist.");
            }
            while (count(self::$channels[$this->id]) >= $this->capacity) {
                yield;
            }
            self::$channels[$this->id][] = $data;
            return $data;
        };

        $result = new Result($fn());
        return $result;
    }

    public function receive(): Result
    {
        $fn = function (): Generator {
            while (!isset(self::$channels[$this->id]) || empty(self::$channels[$this->id])) {
                yield;
            }
            $data = array_shift(self::$channels[$this->id]);
            return $data;
        };

        return new Result($fn());
    }


    public function close(): void
    {
        if (isset(self::$channels[$this->id])) {
            unset(self::$channels[$this->id]);
        }
    }
}