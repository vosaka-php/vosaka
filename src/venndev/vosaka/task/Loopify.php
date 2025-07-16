<?php

declare(strict_types=1);

namespace venndev\vosaka\task;

use Generator;
use venndev\vosaka\core\Future;
use venndev\vosaka\core\Result;
use venndev\vosaka\sync\CancelToken;
use venndev\vosaka\time\Sleep;

final class Loopify
{
    private mixed $callback;
    private CancelToken $cancelToken;
    private float $interval = 0.0;
    private array $maps = [];

    public function __construct(Generator|callable $callback)
    {
        $this->callback = $callback;
        $this->cancelToken = new CancelToken();
    }

    /**
     * Factory method to create a new Loopify instance.
     *
     * This method allows you to create a new Loopify instance with a callback
     * that can be a Generator or a callable. It provides a convenient way
     * to instantiate the Loopify without needing to use the constructor directly.
     *
     * @param Generator|callable $callback The callback to execute in the loop
     * @return self A new instance of Loopify
     */
    public static function new(Generator|callable $callback): self
    {
        return new self($callback);
    }

    /**
     * Set the interval for the loop execution.
     *
     * This method allows you to specify a time interval (in seconds) between
     * each execution of the callback. If the interval is set to 0.0, the loop
     * will execute as fast as possible without any delay.
     *
     * @param float $interval The interval in seconds
     * @return self The current instance for method chaining
     */
    public function interval(float $interval): self
    {
        $this->interval = $interval;
        return $this;
    }

    /**
     * Map a callback to the result of the loop.
     *
     * This method allows you to apply a transformation to the result of the
     * loop's callback. The provided callback will be called with the result
     * of the loop, and its return value will be used as the new result.
     *
     * @param callable $callback The mapping function to apply
     * @return self The current instance for method chaining
     */
    public function map(callable $callback): self
    {
        $this->maps[] = $callback;
        return $this;
    }

    /**
     * Cancel the loop execution.
     *
     * This method allows you to cancel the loop execution. Once called, the
     * loop will stop executing further iterations and will exit gracefully.
     */
    public function cancel(): void
    {
        $this->cancelToken->cancel();
    }

    /**
     * Wait for the loop to complete and return the result.
     *
     * This method starts the loop execution and returns a Future that will
     * resolve with the final result of the loop. The loop will continue to
     * execute until it is cancelled or the callback completes.
     *
     * The result can be transformed using any mapping functions provided
     * through the `map` method.
     *
     * @return Result A Future that resolves with the final result of the loop
     */
    public function wait(): Result
    {
        $fn = function (): Generator {
            while (true) {
                $result = null;
                if ($this->callback instanceof Generator) {
                    $result = yield from $this->callback;
                } elseif (is_callable($this->callback)) {
                    yield $result = call_user_func($this->callback);
                }

                foreach ($this->maps as $map) {
                    if (is_callable($map)) {
                        $result = call_user_func($map, $result);
                    } elseif ($map instanceof Generator) {
                        $result = yield from $map;
                    }
                }

                if ($this->interval > 0.0) {
                    yield Sleep::new($this->interval);
                }

                if ($this->cancelToken->isCancelled()) {
                    break;
                }
            }
        };

        return Future::new($fn());
    }
}
