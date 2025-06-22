<?php

declare(strict_types=1);

namespace venndev\vosaka\time;

use Closure;
use Generator;
use venndev\vosaka\sync\CancelToken;
use venndev\vosaka\VOsaka;

final class Repeat
{
    private Generator|Closure $callback;
    private CancelToken $cancelToken;

    public function __construct(callable|Generator|Closure $callback)
    {
        if (is_callable($callback)) {
            $callback = Closure::fromCallable($callback);
        }

        $this->callback = $callback;
        $this->cancelToken = new CancelToken();
    }

    public static function c(callable|Generator|Closure $callback): self
    {
        return new self($callback);
    }

    public function __invoke(int $seconds): self
    {
        VOsaka::spawn(function () use ($seconds): Generator {
            while (true) {
                yield Sleep::c($seconds);

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

    public function cancel(): void
    {
        $this->cancelToken->cancel();
    }
}