<?php

declare(strict_types=1);

namespace venndev\vosaka\process;

use Generator;
use Throwable;
use venndev\vosaka\core\Result;
use venndev\vosaka\utils\string\StrCmd;

final class Command
{
    use StrCmd;

    private Process $process;
    private array $descriptorSpec = [];

    public function __construct(string $command)
    {
        $this->command = $command;
        $this->process = new Process();
    }

    public static function c(string $command): self
    {
        return new self($command);
    }

    public function stdin(array $descriptorSpec): self
    {
        if (empty($this->descriptorSpec)) {
            $this->descriptorSpec = Stdio::piped();
        }

        $this->descriptorSpec[0] = $descriptorSpec;

        return $this;
    }

    public function stdout(array $descriptorSpec): self
    {
        if (empty($this->descriptorSpec)) {
            $this->descriptorSpec = Stdio::piped();
        }

        $this->descriptorSpec[1] = $descriptorSpec;

        return $this;
    }

    public function stderr(array $descriptorSpec): self
    {
        if (empty($this->descriptorSpec)) {
            $this->descriptorSpec = Stdio::piped();
        }

        $this->descriptorSpec[2] = $descriptorSpec;

        return $this;
    }

    public function spawn(): Result
    {
        $fn = function (): Generator {
            try {
                if (empty($this->descriptorSpec)) {
                    $this->descriptorSpec = Stdio::piped();
                }

                yield $this->process->start(
                    $this->command,
                    $this->descriptorSpec
                );

                return $this;
            } catch (Throwable $e) {
                return $e;
            }
        };

        return new Result($fn());
    }

    public function wait(): Result
    {
        $fn = function (): Generator {
            try {
                $result = yield from $this->process->handle()->unwrap();
                return $result;
            } catch (Throwable $e) {
                return $e;
            }
        };

        return new Result($fn());
    }

    public function kill(): Result
    {
        $fn = function (): Generator {
            try {
                yield from $this->process->stop()->unwrap();
                return true;
            } catch (Throwable $e) {
                return $e;
            }
        };

        return new Result($fn());
    }
}