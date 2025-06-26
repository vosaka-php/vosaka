<?php

declare(strict_types=1);

namespace venndev\vosaka\process;

use Generator;
use Throwable;
use venndev\vosaka\core\Result;
use venndev\vosaka\utils\string\StrCmd;

/**
 * Command class for executing external processes asynchronously.
 *
 * Provides a high-level interface for spawning and managing external processes
 * in an asynchronous manner. Supports configurable stdin, stdout, and stderr
 * redirection, process spawning, waiting for completion, and termination.
 *
 * The class uses the Process class internally but provides a more convenient
 * fluent interface for common process operations. All operations return Result
 * objects that can be awaited using VOsaka's async runtime.
 */
final class Command
{
    use StrCmd;

    private Process $process;
    private array $descriptorSpec = [];

    /**
     * Constructor for Command.
     *
     * Creates a new Command instance with the specified command string.
     * The command will be executed when spawn() is called. Descriptor
     * specifications can be configured before spawning using the fluent
     * interface methods.
     *
     * @param string $command The command string to execute
     */
    public function __construct(string $command)
    {
        $this->command = $command;
        $this->process = new Process();
    }

    /**
     * Create a new Command instance (factory method).
     *
     * Convenience factory method for creating Command instances.
     * The 'c' stands for 'create' and provides a shorter syntax
     * for command creation.
     *
     * @param string $command The command string to execute
     * @return self A new Command instance
     */
    public static function c(string $command): self
    {
        return new self($command);
    }

    /**
     * Configure stdin descriptor for the process.
     *
     * Sets the stdin descriptor specification for the process. If no
     * descriptor specifications have been set yet, initializes them
     * with piped defaults. The descriptor spec should follow PHP's
     * proc_open() format.
     *
     * @param array $descriptorSpec Descriptor specification for stdin
     * @return self This Command instance for method chaining
     */
    public function stdin(array $descriptorSpec): self
    {
        if (empty($this->descriptorSpec)) {
            $this->descriptorSpec = Stdio::piped();
        }

        $this->descriptorSpec[0] = $descriptorSpec;

        return $this;
    }

    /**
     * Configure stdout descriptor for the process.
     *
     * Sets the stdout descriptor specification for the process. If no
     * descriptor specifications have been set yet, initializes them
     * with piped defaults. The descriptor spec should follow PHP's
     * proc_open() format.
     *
     * @param array $descriptorSpec Descriptor specification for stdout
     * @return self This Command instance for method chaining
     */
    public function stdout(array $descriptorSpec): self
    {
        if (empty($this->descriptorSpec)) {
            $this->descriptorSpec = Stdio::piped();
        }

        $this->descriptorSpec[1] = $descriptorSpec;

        return $this;
    }

    /**
     * Configure stderr descriptor for the process.
     *
     * Sets the stderr descriptor specification for the process. If no
     * descriptor specifications have been set yet, initializes them
     * with piped defaults. The descriptor spec should follow PHP's
     * proc_open() format.
     *
     * @param array $descriptorSpec Descriptor specification for stderr
     * @return self This Command instance for method chaining
     */
    public function stderr(array $descriptorSpec): self
    {
        if (empty($this->descriptorSpec)) {
            $this->descriptorSpec = Stdio::piped();
        }

        $this->descriptorSpec[2] = $descriptorSpec;

        return $this;
    }

    /**
     * Spawn the process asynchronously.
     *
     * Starts the external process with the configured command and descriptor
     * specifications. If no descriptors have been configured, uses piped
     * defaults for stdin, stdout, and stderr. Returns a Result that can be
     * awaited to get the Command instance back once the process is started.
     *
     * The process runs asynchronously and doesn't block the event loop.
     * Use wait() to wait for the process to complete.
     *
     * @return Result A Result containing this Command instance or an error
     */
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

    /**
     * Wait for the process to complete asynchronously.
     *
     * Waits for the spawned process to finish execution and returns its
     * result. This method should be called after spawn() to wait for
     * the process completion. The operation is asynchronous and won't
     * block the event loop.
     *
     * @return Result A Result containing the process result or an error
     */
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

    /**
     * Kill the running process asynchronously.
     *
     * Terminates the spawned process if it's currently running. This is
     * useful for stopping long-running processes or cleaning up when
     * a process needs to be aborted. The operation is asynchronous and
     * returns a Result indicating success or failure.
     *
     * @return Result A Result containing true on success or an error
     */
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
