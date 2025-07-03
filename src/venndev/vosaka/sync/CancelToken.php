<?php

declare(strict_types=1);

namespace venndev\vosaka\sync;

use Generator;
use venndev\vosaka\utils\sync\CancelFuture;

/**
 * CancelToken class for managing cancellation of asynchronous operations.
 *
 * Provides a mechanism for signaling cancellation to asynchronous tasks and
 * operations. Tasks can check for cancellation status and respond appropriately
 * by cleaning up resources and terminating early. Supports both simple
 * cancellation and cancellation with custom values.
 *
 * The class maintains a static registry of all tokens to ensure cancellation
 * state is preserved across async boundaries. Each token has a unique ID and
 * can be cancelled from any context.
 */
final class CancelToken
{
    private bool $isCancelled = false;
    private mixed $cancelledValue = null;
    private static int $nextId = 0;
    private int $id;

    /**
     * Static registry of all active cancel tokens.
     * @var array<int, CancelToken>
     */
    private static array $tokens = [];

    /**
     * Constructor for CancelToken.
     *
     * Creates a new cancel token with a unique ID and registers it in the
     * static token registry. The token starts in a non-cancelled state.
     */
    public function __construct()
    {
        $this->id = self::$nextId++;
        self::$tokens[$this->id] = $this;
    }

    /**
     * Create a new CancelToken instance.
     *
     * This static method is a factory for creating new CancelToken objects.
     * It initializes the token and registers it in the static registry.
     *
     * @return CancelToken A new instance of CancelToken
     */
    public static function new(): CancelToken
    {
        return new self();
    }

    /**
     * Cancel the token without a specific value.
     *
     * Marks this token as cancelled, which will cause any operations checking
     * this token to detect the cancellation and respond appropriately. The
     * cancellation state is persisted in the static registry.
     *
     * @return void
     */
    public function cancel(): void
    {
        $this->isCancelled = true;
        $this->save();
    }

    /**
     * Cancel the token with a specific cancellation value.
     *
     * Similar to cancel() but allows specifying a custom value that can be
     * retrieved by operations checking for cancellation. This is useful for
     * providing context about why the cancellation occurred or passing
     * data along with the cancellation signal.
     *
     * @param mixed $value The value to associate with the cancellation
     * @return void
     */
    public function cancelWithValue(mixed $value): void
    {
        $this->isCancelled = true;
        $this->cancelledValue = $value;
        $this->save();
    }

    /**
     * Create a cancellation future that can be yielded in generators.
     *
     * Returns a generator that yields a CancelFuture object. This can be
     * used in async operations to create cancellation points where the
     * operation can be terminated early if cancellation is requested.
     *
     * Note: There appears to be a typo in the method name - it should likely
     * be "cancelFuture" rather than "cancelFurture".
     *
     * @return Generator A generator that yields a CancelFuture
     */
    public function cancelFurture(): Generator
    {
        return yield new CancelFuture();
    }

    /**
     * Check if this token has been cancelled.
     *
     * Returns true if the token has been cancelled via cancel() or
     * cancelWithValue(), false otherwise. This method checks the current
     * state from the static registry to ensure accuracy across async
     * boundaries.
     *
     * @return bool True if the token is cancelled, false otherwise
     */
    public function isCancelled(): bool
    {
        return self::$tokens[$this->id]->isCancelled;
    }

    /**
     * Close and cleanup the cancel token.
     *
     * Removes this token from the static registry and cleans up its resources.
     * After calling close(), the token should not be used anymore. This is
     * important for preventing memory leaks in long-running applications.
     *
     * @return void
     */
    public function close(): void
    {
        if (isset(self::$tokens[$this->id])) {
            unset(self::$tokens[$this->id]);
        }
    }

    /**
     * Save the current token state to the static registry.
     *
     * Internal method that updates the token's state in the static registry.
     * This ensures that cancellation state is preserved and accessible
     * across different async contexts and execution boundaries.
     *
     * @return void
     */
    private function save(): void
    {
        self::$tokens[$this->id] = $this;
    }
}
