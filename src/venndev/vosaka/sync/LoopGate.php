<?php

declare(strict_types=1);

namespace venndev\vosaka\sync;

/**
 * LoopGate is a simple synchronization primitive that allows
 * a task to proceed only after a specified number of ticks.
 * It can be used to control the flow of tasks in a loop.
 */
final class LoopGate
{
    private int $n;
    private int $counter = 0;

    public function __construct(int $n)
    {
        $this->n = $n;
    }

    /**
     * Creates a new LoopGate instance.
     *
     * @param int $n The number of ticks after which the gate opens.
     * @return LoopGate
     */
    public static function new(int $n): LoopGate
    {
        return new self($n);
    }

    /**
     * Ticks the gate. If the number of ticks reaches `n`, it resets
     * the counter and returns true, allowing the task to proceed.
     *
     * @return bool True if the gate opens, false otherwise.
     */
    public function tick(): bool
    {
        $this->counter++;
        if ($this->counter >= $this->n) {
            $this->counter = 0;
            return true;
        }
        return false;
    }
}
