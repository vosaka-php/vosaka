<?php

declare(strict_types=1);

namespace venndev\vosaka\time;

use Closure;
use Generator;
use venndev\vosaka\sync\CancelToken;
use venndev\vosaka\VOsaka;

/**
 * Repeat class for executing recurring asynchronous operations.
 *
 * This class provides functionality to repeatedly execute a callback or generator
 * at specified intervals. The execution runs in its own spawned task and can be
 * cancelled at any time using the built-in cancellation token. Supports both
 * regular callables and generator functions.
 *
 * The repeat operation continues indefinitely until explicitly cancelled,
 * making it suitable for periodic tasks, monitoring operations, or any
 * recurring background work that needs to run alongside other async tasks.
 */
final class Repeat
{
    private Generator|Closure $callback;
    private CancelToken $cancelToken;

    /**
     * Constructor for Repeat.
     *
     * Creates a new Repeat instance with the specified callback. The callback
     * can be any callable, a Closure, or a Generator. Callables are converted
     * to Closures for consistent handling. A cancellation token is automatically
     * created to allow stopping the repeat operation.
     *
     * @param callable|Generator|Closure $callback The callback to execute repeatedly
     */
    public function __construct(callable|Generator|Closure $callback)
    {
        if (is_callable($callback)) {
            $callback = Closure::fromCallable($callback);
        }

        $this->callback = $callback;
        $this->cancelToken = new CancelToken();
    }

    /**
     * Create a new Repeat instance (factory method).
     *
     * Convenience factory method for creating Repeat instances.
     * The 'c' stands for 'create' and provides a shorter syntax
     * for creating repeating operations.
     *
     * @param callable|Generator|Closure $callback The callback to execute repeatedly
     * @return self A new Repeat instance
     */
    public static function new(callable|Generator|Closure $callback): self
    {
        return new self($callback);
    }

    /**
     * Start the repeat operation with the specified interval.
     *
     * Makes the Repeat instance callable and starts the repeating execution
     * with the given interval in seconds. The callback will be executed
     * repeatedly with the specified delay between executions. The operation
     * runs in a separate spawned task and continues until cancelled.
     *
     * The method handles different callback types appropriately:
     * - Closures are called directly
     * - Generators are yielded from to allow async operations
     * - Other callables are invoked using call_user_func
     *
     * @param int $seconds The interval between executions in seconds
     * @return self This Repeat instance for method chaining
     */
    public function __invoke(int $seconds): self
    {
        VOsaka::spawn(function () use ($seconds): Generator {
            while (true) {
                yield Sleep::new($seconds);

                if ($this->callback instanceof Closure) {
                    ($this->callback)();
                } elseif ($this->callback instanceof Generator) {
                    yield from $this->callback;
                } else {
                    call_user_func($this->callback);
                }

                if ($this->cancelToken->isCancelled()) {
                    break;
                }
            }
        });
        return $this;
    }

    /**
     * Cancel the repeat operation.
     *
     * Stops the repeating execution by cancelling the internal cancellation
     * token. The current execution cycle will complete, but no further
     * executions will be scheduled. This provides a clean way to stop
     * long-running repeat operations.
     *
     * @return void
     */
    public function cancel(): void
    {
        $this->cancelToken->cancel();
    }
}
