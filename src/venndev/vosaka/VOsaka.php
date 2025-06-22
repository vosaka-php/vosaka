<?php

declare(strict_types=1);

namespace venndev\vosaka;

use Generator;
use InvalidArgumentException;
use RuntimeException;
use Throwable;
use venndev\vosaka\eventloop\EventLoop;
use venndev\vosaka\time\Sleep;

final class VOsaka
{
    private static EventLoop $eventLoop;

    private static function getLoop(): EventLoop
    {
        if (!isset(self::$eventLoop)) {
            self::$eventLoop = new EventLoop();
        }
        return self::$eventLoop;
    }

    public static function setMaximumPeriod(int $maxTasks): void
    {
        self::getLoop()->setMaximumPeriod($maxTasks);
    }

    public static function enableMaximumPeriod(): void
    {
        self::getLoop()->setEnableMaximumPeriod(true);
    }

    public static function disableMaximumPeriod(): void
    {
        self::getLoop()->setEnableMaximumPeriod(false);
    }

    public static function spawn(callable|Generator $task, mixed $context = null): void
    {
        self::getLoop()->spawn($task, $context);
    }

    public static function join(callable|Generator ...$tasks): void
    {
        foreach ($tasks as $task) {
            self::getLoop()->spawn($task);
        }
    }

    public static function select(callable|Generator ...$tasks): void
    {
        self::getLoop()->select($tasks);
    }

    public static function retry(
        callable $taskFactory,
        int $maxRetries = 3,
        int $delaySeconds = 1,
        int $backOffMultiplier = 2,
        ?callable $shouldRetry = null
    ): Generator {
        $retries = 0;

        while ($retries < $maxRetries) {
            try {
                $task = $taskFactory();
                if (!$task instanceof Generator) {
                    throw new InvalidArgumentException('Task must return a Generator');
                }

                return yield from $task;
            } catch (Throwable $e) {
                if ($shouldRetry && !$shouldRetry($e)) {
                    throw $e;
                }

                $retries++;
                if ($retries >= $maxRetries) {
                    throw new RuntimeException("Task failed after {$maxRetries} retries", 0, $e);
                }

                $delay = (int) ($delaySeconds * pow($backOffMultiplier, $retries - 1));
                yield Sleep::c($delay);
            }
        }
    }

    public static function run(): void
    {
        self::getLoop()->run();
    }

    public static function close(): void
    {
        self::getLoop()->close();
    }
}