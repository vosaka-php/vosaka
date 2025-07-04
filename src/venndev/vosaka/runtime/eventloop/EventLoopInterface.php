<?php

declare(strict_types=1);

namespace venndev\vosaka\runtime\eventloop;

interface EventLoopInterface
{
    public function addReadStream($stream, callable $listener): void;
    public function addWriteStream($stream, callable $listener): void;
    public function removeReadStream($stream): void;
    public function removeWriteStream($stream): void;
    public function addTimer(float $interval, callable $callback, bool $repeat = false): void;
    public function addSignal(int $signal, callable $listener): void;
    public function removeSignal(int $signal, callable $listener): void;
    public function run(): void;
    public function stop(): void;
}