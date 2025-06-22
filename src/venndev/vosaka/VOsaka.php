<?php

declare(strict_types=1);

namespace venndev\vosaka;

use Generator;
use InvalidArgumentException;
use RuntimeException;
use Throwable;
use venndev\vosaka\eventloop\EventLoop;
use venndev\vosaka\time\Sleep;
use venndev\vosaka\utils\Result;

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

    private static function processAllTasks(callable|Generator ...$tasks): Generator
    {
        foreach ($tasks as $task) {
            $task = $task instanceof Generator ? $task : fn() => yield $task;
            yield from $task;
        }
    }

    public static function join(callable|Generator ...$tasks): Generator
    {
        yield from self::processAllTasks(...$tasks);
    }

    public static function tryJoin(callable|Generator ...$tasks): Result
    {
        $fn = function () use ($tasks): Generator {
            try {
                yield from self::processAllTasks(...$tasks);
            } catch (Throwable $e) {
                return $e;
            }
            return null;
        };

        return new Result($fn());
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