<?php

declare(strict_types=1);

namespace venndev\vosaka\core;

use Generator;
use RuntimeException;
use Throwable;
use Error;

/**
 * Result class for handling asynchronous task results and transformations.
 *
 * This class wraps generator-based tasks and provides a fluent interface for
 * result handling, transformation, and error management. It supports:
 *
 * - Checking if results are successful with isOk()
 * - Chaining transformations with map()
 * - Unwrapping results with various error handling strategies
 * - Providing default values for failed operations
 *
 * The Result class is central to VOsaka's error handling strategy, allowing
 * for composable and predictable async operation results.
 */
final class Result
{
    private array $callbacks = [];
    private mixed $result = null;

    /**
     * Constructor for Result wrapper.
     *
     * Creates a new Result instance that wraps a Generator task. The task
     * will be executed when one of the unwrap methods is called, and any
     * registered callbacks will be applied to transform the result.
     *
     * @param Generator $task The generator task to wrap
     */
    public function __construct(public readonly Generator $task)
    {
        // TODO: Implement the logic for handling the task.
    }

    public static function c(Generator $task): Result
    {
        return new self($task);
    }

    /**
     * Check if the result is successful (not an instance of Throwable or Error).
     *
     * Examines the current value of the wrapped task to determine if it
     * represents a successful result. Returns false if the current value
     * is any kind of exception or error.
     *
     * @return bool True if the result is successful, false otherwise
     */
    public function isOk(): bool
    {
        $result = $this->task->current();
        return !($result instanceof Throwable || $result instanceof Error);
    }

    /**
     * Add a callback to be executed on the result for transformation.
     *
     * Registers a callback that will be executed when the result is unwrapped.
     * Callbacks are executed in the order they were added, with each callback
     * receiving the result of the previous callback. Supports method chaining
     * for composing multiple transformations.
     *
     * If a callback returns a Generator, it will be properly awaited.
     * If a callback throws an exception, the transformation chain stops
     * and the exception becomes the final result.
     *
     * @param callable $callback The callback to execute on the result
     * @return Result The current Result instance for method chaining
     */
    public function map(callable $callback): Result
    {
        $this->callbacks[] = $callback;
        return $this;
    }

    /**
     * Execute all registered callbacks on the result in sequence.
     *
     * Internal method that applies all registered transformation callbacks
     * to the result in the order they were added. Each callback receives
     * the output of the previous callback, creating a transformation pipeline.
     *
     * If any callback returns a Generator, it is properly awaited before
     * passing its result to the next callback. If any callback throws an
     * exception, the transformation chain stops and returns the exception.
     *
     * @param mixed $result The initial result to transform
     * @return Generator The final transformed result or a Throwable if an error occurred
     */
    private function executeCallbacks(mixed $result): Generator
    {
        foreach ($this->callbacks as $callback) {
            try {
                $result = $callback($result);
                if ($result instanceof Generator) {
                    $result = yield from $result;
                    $this->result = $result;
                }
            } catch (Throwable $e) {
                return $e;
            }
        }
        return $result;
    }

    /**
     * Unwrap the result, executing all callbacks and returning the final value.
     *
     * Executes the wrapped task, applies all registered transformation callbacks,
     * and returns the final result. If the task or any callback produces an
     * exception, that exception is thrown.
     *
     * This is the primary method for extracting values from Result instances
     * when you want exceptions to propagate normally.
     *
     * @return Generator The final value after executing all callbacks
     * @throws Throwable If the task fails or any callback throws an exception
     */
    public function unwrap(): Generator
    {
        $result = yield from $this->task;
        $transformedResult = yield from $this->executeCallbacks($result);

        if ($transformedResult instanceof Throwable) {
            throw $transformedResult;
        }

        return $transformedResult;
    }

    /**
     * Unwrap the result, returning a default value if the result is an error.
     *
     * Similar to unwrap() but provides graceful error handling by returning
     * a default value instead of throwing exceptions. Executes the task and
     * applies all transformation callbacks, but if any step produces an
     * exception, returns the provided default value instead.
     *
     * This is useful when you want to provide fallback values for failed
     * operations without having to handle exceptions explicitly.
     *
     * @param mixed $default The default value to return if the result is an error
     * @return Generator The final transformed value or the default value
     */
    public function unwrapOr(mixed $default): Generator
    {
        $result = yield from $this->task;
        $transformedResult = yield from $this->executeCallbacks($result);

        return $transformedResult instanceof Throwable
            ? $default
            : $transformedResult;
    }

    /**
     * Unwrap the result with a custom error message.
     *
     * Similar to unwrap() but allows you to provide a custom error message
     * that will be used if the operation fails. If the task or any callback
     * produces an exception, a new RuntimeException is thrown with your
     * custom message, while the original exception is preserved as the cause.
     *
     * This is useful for providing context-specific error messages that
     * help with debugging and error reporting.
     *
     * @param string $message Custom error message to throw if the result is an error
     * @return Generator The final value after executing all callbacks
     * @throws RuntimeException If the result is an error, throws with the custom message
     */
    public function expect(string $message): Generator
    {
        $result = yield from $this->task;
        $transformedResult = yield from $this->executeCallbacks($result);

        if ($transformedResult instanceof Throwable) {
            throw new RuntimeException($message, 0, $transformedResult);
        }

        return $transformedResult;
    }

    /**
     * Invoke the Result as a callable, returning the result or error message.
     *
     * Makes the Result instance callable by implementing __invoke(). When called,
     * executes the task and applies transformations, but instead of throwing
     * exceptions, returns the error message as a string if an error occurs.
     *
     * This provides a convenient way to extract either the successful result
     * or a string representation of any error that occurred, without having
     * to handle exceptions.
     *
     * @return Generator The final result value or the error message string if an error occurred
     */
    public function __invoke(): Generator
    {
        $result = yield from $this->task;
        $transformedResult = yield from $this->executeCallbacks($result);

        return $transformedResult instanceof Throwable
            ? $transformedResult->getMessage()
            : $transformedResult;
    }
}
