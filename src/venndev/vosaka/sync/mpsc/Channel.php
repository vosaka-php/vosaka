<?php

declare(strict_types=1);

namespace venndev\vosaka\sync;

use Generator;
use RuntimeException;
use venndev\vosaka\core\Result;
use venndev\vosaka\VOsaka;

/**
 * A simple MPSC (Multiple Producer Single Consumer) channel implementation.
 * This channel allows multiple producers to send data to a single consumer.
 * It supports a fixed capacity, and blocks the producer if the channel is full.
 */
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

    public static function new(?int $capacity = null): self
    {
        return new self($capacity);
    }

    public function send(mixed $data): Result
    {
        $fn = function () use ($data): Generator {
            if (! isset(self::$channels[$this->id])) {
                throw new RuntimeException(
                    "Channel {$this->id} does not exist."
                );
            }

            while (count(self::$channels[$this->id]) >= $this->capacity) {
                yield;
            }

            self::$channels[$this->id][] = $data;
            return $data;
        };

        return VOsaka::spawn($fn());
    }

    public function receive(): Result
    {
        $fn = function (): Generator {
            while (
                ! isset(self::$channels[$this->id]) ||
                empty(self::$channels[$this->id])
            ) {
                yield;
            }
            $data = array_shift(self::$channels[$this->id]);
            return $data;
        };

        return VOsaka::spawn($fn());
    }

    public function close(): void
    {
        if (isset(self::$channels[$this->id])) {
            unset(self::$channels[$this->id]);
        }
    }
}
