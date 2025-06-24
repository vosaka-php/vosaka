<?php

declare(strict_types=1);

namespace venndev\vosaka\tracing;

use Closure;
use Generator;
use Throwable;
use venndev\vosaka\utils\Defer;
use venndev\vosaka\time\Sleep;
use venndev\vosaka\VOsaka;

/**
 * TraceHelper - Provides easy integration with VOsaka operations
 */
final class TraceHelper
{
    private static ?Tracer $tracer = null;

    /**
     * Initialize tracing for VOsaka
     */
    public static function init(array $config = []): void
    {
        self::$tracer = Tracer::getInstance();
        self::$tracer->enable($config);
    }

    /**
     * Get the tracer instance
     */
    public static function getTracer(): ?Tracer
    {
        return self::$tracer;
    }

    /**
     * Trace Sleep::c operations
     */
    public static function traceSleep(int $seconds, array $tags = []): Generator
    {
        if (!self::$tracer || !self::$tracer->isEnabled()) {
            yield Sleep::c($seconds);
            return;
        }

        $spanId = self::$tracer->startSpan('vosaka.sleep', array_merge([
            'operation_type' => 'sleep',
            'duration_seconds' => $seconds
        ], $tags));

        try {
            self::$tracer->log("Starting sleep for {$seconds} seconds");
            yield Sleep::c($seconds);
            self::$tracer->log("Sleep completed");
            self::$tracer->finishSpan($spanId, ['completed' => true]);
        } catch (Throwable $e) {
            self::$tracer->recordError($e, $spanId);
            self::$tracer->finishSpan($spanId, ['error' => true]);
            throw $e;
        }
    }

    /**
     * Trace VOsaka::spawn operations
     */
    public static function traceAwait(Generator $generator, string $operationName = 'unknown', array $tags = []): Generator
    {
        if (!self::$tracer || !self::$tracer->isEnabled()) {
            yield from VOsaka::spawn($generator)->unwrap();
            return;
        }

        $spanId = self::$tracer->startSpan('vosaka.await', array_merge([
            'operation_type' => 'await',
            'target_operation' => $operationName
        ], $tags));

        try {
            self::$tracer->log("Starting await for operation: {$operationName}");
            $result = yield from VOsaka::spawn($generator)->unwrap();
            self::$tracer->log("Await completed successfully", ['result_type' => gettype($result)]);
            self::$tracer->finishSpan($spanId, ['completed' => true]);
            return $result;
        } catch (Throwable $e) {
            self::$tracer->recordError($e, $spanId);
            self::$tracer->finishSpan($spanId, ['error' => true]);
            throw $e;
        }
    }

    /**
     * Trace VOsaka::spawn operations
     */
    public static function traceSpawn(Generator $generator, string $operationName = 'unknown', array $tags = []): void
    {
        if (!self::$tracer || !self::$tracer->isEnabled()) {
            VOsaka::spawn($generator)->unwrap();
            return;
        }

        $spanId = self::$tracer->startSpan('vosaka.spawn', array_merge([
            'operation_type' => 'spawn',
            'target_operation' => $operationName
        ], $tags));

        try {
            self::$tracer->log("Spawning operation: {$operationName}");

            // Wrap the generator to trace its execution
            $tracedGenerator = self::wrapGeneratorWithTrace($generator, $operationName);
            VOsaka::spawn($tracedGenerator)->unwrap();

            self::$tracer->log("Spawn initiated successfully");
            self::$tracer->finishSpan($spanId, ['spawned' => true]);
        } catch (Throwable $e) {
            self::$tracer->recordError($e, $spanId);
            self::$tracer->finishSpan($spanId, ['error' => true]);
            throw $e;
        }
    }

    /**
     * Trace VOsaka::join operations
     */
    public static function traceJoin(array $generators, array $operationNames = [], array $tags = []): void
    {
        if (!self::$tracer || !self::$tracer->isEnabled()) {
            VOsaka::join(...$generators);
            return;
        }

        $spanId = self::$tracer->startSpan('vosaka.join', array_merge([
            'operation_type' => 'join',
            'generator_count' => count($generators)
        ], $tags));

        try {
            self::$tracer->log("Starting join with " . count($generators) . " generators");

            // Wrap each generator with tracing
            $tracedGenerators = [];
            foreach ($generators as $index => $generator) {
                $operationName = $operationNames[$index] ?? "generator_{$index}";
                $tracedGenerators[] = self::wrapGeneratorWithTrace($generator, $operationName);
            }

            VOsaka::join(...$tracedGenerators);
            self::$tracer->log("Join completed");
            self::$tracer->finishSpan($spanId, ['completed' => true]);
        } catch (Throwable $e) {
            self::$tracer->recordError($e, $spanId);
            self::$tracer->finishSpan($spanId, ['error' => true]);
            throw $e;
        }
    }

    /**
     * Trace VOsaka::select operations
     */
    public static function traceSelect(array $generators, array $operationNames = [], array $tags = []): void
    {
        if (!self::$tracer || !self::$tracer->isEnabled()) {
            VOsaka::select(...$generators);
            return;
        }

        $spanId = self::$tracer->startSpan('vosaka.select', array_merge([
            'operation_type' => 'select',
            'generator_count' => count($generators)
        ], $tags));

        try {
            self::$tracer->log("Starting select with " . count($generators) . " generators");

            // Wrap each generator with tracing
            $tracedGenerators = [];
            foreach ($generators as $index => $generator) {
                $operationName = $operationNames[$index] ?? "generator_{$index}";
                $tracedGenerators[] = self::wrapGeneratorWithTrace($generator, $operationName);
            }

            VOsaka::select(...$tracedGenerators);
            self::$tracer->log("Select completed");
            self::$tracer->finishSpan($spanId, ['completed' => true]);
        } catch (Throwable $e) {
            self::$tracer->recordError($e, $spanId);
            self::$tracer->finishSpan($spanId, ['error' => true]);
            throw $e;
        }
    }

    /**
     * Trace Defer::c operations
     */
    public static function traceDefer(Closure $callback, string $operationName = 'deferred_callback', array $tags = []): Generator
    {
        if (!self::$tracer || !self::$tracer->isEnabled()) {
            yield Defer::c($callback);
            return;
        }

        $spanId = self::$tracer->startSpan('vosaka.defer', array_merge([
            'operation_type' => 'defer',
            'callback_name' => $operationName
        ], $tags));

        try {
            self::$tracer->log("Registering deferred callback: {$operationName}");

            // Wrap the callback with tracing
            $tracedCallback = function () use ($callback, $operationName) {
                return self::$tracer->traceCallable($callback, "deferred_{$operationName}", [], ['deferred' => true]);
            };

            yield Defer::c($tracedCallback);
            self::$tracer->log("Defer registered successfully");
            self::$tracer->finishSpan($spanId, ['registered' => true]);
        } catch (Throwable $e) {
            self::$tracer->recordError($e, $spanId);
            self::$tracer->finishSpan($spanId, ['error' => true]);
            throw $e;
        }
    }

    /**
     * Trace VOsaka::run operations
     */
    public static function traceRun(array $tags = []): void
    {
        if (!self::$tracer || !self::$tracer->isEnabled()) {
            VOsaka::run();
            return;
        }

        $spanId = self::$tracer->startSpan('vosaka.run', array_merge([
            'operation_type' => 'run'
        ], $tags));

        try {
            self::$tracer->log("Starting VOsaka event loop");
            $startMemory = memory_get_usage(true);

            VOsaka::run();

            $endMemory = memory_get_usage(true);
            self::$tracer->log("Event loop completed", [
                'memory_usage_delta' => $endMemory - $startMemory
            ]);
            self::$tracer->finishSpan($spanId, ['completed' => true]);
        } catch (Throwable $e) {
            self::$tracer->recordError($e, $spanId);
            self::$tracer->finishSpan($spanId, ['error' => true]);
            throw $e;
        }
    }

    /**
     * Create a traced version of a custom generator function
     */
    public static function trace(Generator $generator, string $operationName, array $tags = []): Generator
    {
        if (!self::$tracer || !self::$tracer->isEnabled()) {
            yield from $generator;
            return;
        }

        yield from self::$tracer->traceGenerator($generator, $operationName, $tags);
    }

    /**
     * Add custom log entry to current trace
     */
    public static function log(string $message, array $fields = []): void
    {
        if (self::$tracer && self::$tracer->isEnabled()) {
            self::$tracer->log($message, $fields);
        }
    }

    /**
     * Add tags to current trace
     */
    public static function tag(array $tags): void
    {
        if (self::$tracer && self::$tracer->isEnabled()) {
            self::$tracer->tag($tags);
        }
    }

    /**
     * Get trace statistics
     */
    public static function getStats(): array
    {
        if (!self::$tracer) {
            return [];
        }
        return self::$tracer->getStats();
    }

    /**
     * Export traces
     */
    public static function export(string $format = 'json'): string
    {
        if (!self::$tracer) {
            return '';
        }
        return self::$tracer->export($format);
    }

    /**
     * Flush traces to file
     */
    public static function flush(): string
    {
        if (!self::$tracer) {
            return '';
        }
        return self::$tracer->flush();
    }

    /**
     * Clear all traces
     */
    public static function clear(): void
    {
        if (self::$tracer) {
            self::$tracer->clear();
        }
    }

    // Private helper methods

    /**
     * Wrap a generator with tracing
     */
    private static function wrapGeneratorWithTrace(Generator $generator, string $operationName): Generator
    {
        if (!self::$tracer || !self::$tracer->isEnabled()) {
            yield from $generator;
            return;
        }

        $spanId = self::$tracer->startSpan("generator.{$operationName}", [
            'type' => 'wrapped_generator',
            'operation' => $operationName
        ]);

        try {
            $yieldCount = 0;
            while ($generator->valid()) {
                $value = $generator->current();
                $yieldCount++;

                if ($yieldCount % 100 === 0) { // Log every 100 yields to avoid spam
                    self::$tracer->log("Generator progress", [
                        'yields' => $yieldCount,
                        'current_value_type' => gettype($value)
                    ]);
                }

                yield $value;
                $generator->next();
            }

            $result = $generator->getReturn();
            self::$tracer->tag(['total_yields' => $yieldCount, 'has_return' => true], $spanId);
            self::$tracer->finishSpan($spanId);

            return $result;
        } catch (Throwable $e) {
            self::$tracer->recordError($e, $spanId);
            self::$tracer->finishSpan($spanId, ['error' => true]);
            throw $e;
        }
    }
}