<?php

declare(strict_types=1);

namespace venndev\vosaka\utils\string;

trait StrCmd
{
    public string $command;

    public function command(string $command): self
    {
        $this->command = $command;
        return $this;
    }

    public function arg(string $arg): self
    {
        $this->command .= ' ' . escapeshellarg($arg);
        return $this;
    }

    public function args(array $args): self
    {
        foreach ($args as $arg) {
            $this->command .= ' ' . escapeshellarg($arg);
        }
        return $this;
    }
}