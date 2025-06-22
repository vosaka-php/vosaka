<?php

declare(strict_types=1);

namespace venndev\vosaka\eventloop;

use Generator;
use InvalidArgumentException;
use SplPriorityQueue;
use Throwable;
use venndev\vosaka\eventloop\scheduler\Defer;
use venndev\vosaka\eventloop\task\TaskPool;
use venndev\vosaka\eventloop\task\TaskState;
use venndev\vosaka\eventloop\task\Task;
use venndev\vosaka\core\MemoryManager;
use venndev\vosaka\time\Interval;
use venndev\vosaka\time\Sleep;
use venndev\vosaka\utils\CallableUtil;
use venndev\vosaka\utils\MemUtil;
use venndev\vosaka\utils\sync\CancelFuture;

final class EventLoop
{
    private SplPriorityQueue $readyQueue;
    private TaskPool $taskPool;
    private ?MemoryManager $memoryManager = null;
    private array $runningTasks = [];
    private array $chainedTasks = [];
    private array $deferredTasks = [];
    private bool $isRunning = false;

    // Memory monitoring
    private int $maxMemoryUsage;
    private int $taskProcessedCount = 0;
    private float $startTime;

    // Maximum tasks to run per period
    private int $maximumPeriod = 20;
    private bool $enableMaximumPeriod = false;

    // Chain ID management
    private static int $nextChainId = 0;

    public function __construct(int $maxMemoryMB = 256)
    {
        $this->readyQueue = new SplPriorityQueue();
        $this->taskPool = new TaskPool();
        $this->maxMemoryUsage = MemUtil::toKB($maxMemoryMB);
    }

    public function getMemoryManager(): MemoryManager
    {
        if ($this->memoryManager === null) {
            $this->memoryManager = new MemoryManager($this->maxMemoryUsage);
        }
        return $this->memoryManager;
    }

    public function spawn(callable|Generator $task, mixed $context = null): int
    {
        $task = CallableUtil::makeAllToCallable($task);
        $task = $this->taskPool->getTask($task, $context);
        $this->readyQueue->insert($task, $task->id);
        return $task->id;
    }

    public function select(callable|Generator ...$tasks): int
    {
        $chainId = self::$nextChainId++ >= PHP_INT_MAX ? 0 : self::$nextChainId;
        foreach ($tasks as $task) {
            $task = CallableUtil::makeAllToCallable($task);
            $task = $this->taskPool->getTask($task);
            $task->chainId = $chainId;
            $this->readyQueue->insert($task, $task->id);
        }
        return $chainId;
    }

    public function run(): void
    {
        $this->startTime = time();
        $this->isRunning = true;

        while (
            $this->hasReadyTasks() ||
            $this->hasRunningTasks() ||
            !empty($this->chainedTasks)
        ) {
            $maxPeriodReached = $this->enableMaximumPeriod &&
                $this->taskProcessedCount >= $this->maximumPeriod;
            if ($maxPeriodReached || !$this->isRunning) {
                break;
            }

            $this->processReadyTasks();
            $this->processRunningTasks();
            $this->processChainedTasks();

            if ($this->memoryManager?->checkMemoryUsage()) {
                $this->memoryManager?->forceGarbageCollection();
            }
        }

        $this->memoryManager?->collectGarbage();
    }

    public function close(): void
    {
        $this->isRunning = false;
    }

    private function hasReadyTasks(): bool
    {
        return !$this->readyQueue->isEmpty();
    }

    private function hasRunningTasks(): bool
    {
        return !empty($this->runningTasks);
    }

    private function processReadyTasks(): void
    {
        while ($this->hasReadyTasks()) {
            $task = $this->readyQueue->extract();
            $this->executeTask($task);
        }
    }

    private function processRunningTasks(): void
    {
        foreach ($this->runningTasks as $task) {
            $this->executeTask($task);
        }
    }

    private function processChainedTasks(): void
    {
        foreach ($this->chainedTasks as $tasks) {
            foreach ($tasks as $task) {
                $this->executeTask($task);
            }
        }
    }

    public function setMaximumPeriod(int $maxTasks): void
    {
        if ($maxTasks <= 0) {
            throw new InvalidArgumentException('Maximum tasks must be a positive integer');
        }
        $this->maximumPeriod = $maxTasks;
    }

    public function setEnableMaximumPeriod(bool $enable): void
    {
        $this->enableMaximumPeriod = $enable;
    }

    private function executeTask(Task $task): void
    {
        $isDone = false;
        try {
            if ($task->state === TaskState::PENDING) {
                $task->state = TaskState::RUNNING;
                if ($task->chainId === null) {
                    $this->runningTasks[$task->id] = $task;
                } else {
                    $this->chainedTasks[$task->chainId][] = $task;
                }
                $task->callback = ($task->callback)($task->context, $this);
            }

            if ($task->state === TaskState::RUNNING) {
                if ($task->callback instanceof Generator) {
                    if (!$task->firstRun) {
                        $task->firstRun = true;
                    } else {
                        $task->callback->next();
                    }

                    $result = $task->callback->current();
                    if (!$task->callback->valid() || $result instanceof CancelFuture) {
                        $isDone = true;
                        $this->completeTask($task, $result);
                    }

                    if ($result instanceof Sleep || $result instanceof Interval) {
                        $task->sleep($result->seconds);
                    }

                    if ($result instanceof Defer) {
                        $this->deferredTasks[$task->id][] = $result;
                    }
                } else {
                    $isDone = true;
                    $this->completeTask($task, $task->callback);
                }
            } elseif ($task->state === TaskState::SLEEPING) {
                $task->tryWake();
            }
        } catch (Throwable $e) {
            $this->failTask($task, $e);
        } finally {
            if (!$isDone) {
                $this->runningTasks[$task->id] = $task;
            }
        }
    }

    private function completeTask(Task $task, mixed $result = null): void
    {
        $task->state = TaskState::COMPLETED;
        $task->result = $result;
        if ($task->chainId !== null) {
            unset($this->chainedTasks[$task->chainId]);
        } else {
            unset($this->runningTasks[$task->id]);
        }
        if (!empty($this->deferredTasks[$task->id])) {
            /**
             * @var Defer $deferredTask
             */
            foreach ($this->deferredTasks[$task->id] as $deferredTask) {
                $this->spawn($deferredTask->callback);
            }
            unset($this->deferredTasks[$task->id]);
        }
    }

    private function failTask(Task $task, Throwable $error): void
    {
        $task->state = TaskState::FAILED;
        $task->error = $error;
        if ($task->chainId !== null) {
            unset($this->chainedTasks[$task->chainId]);
        } else {
            unset($this->runningTasks[$task->id]);
        }
    }
}