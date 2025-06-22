<?php

declare(strict_types=1);

namespace venndev\vosaka\eventloop\task;

enum TaskState: int
{
    case PENDING = 0;
    case RUNNING = 1;
    case SLEEPING = 2;
    case COMPLETED = 3;
    case FAILED = 4;
}