<?php

declare(strict_types=1);

namespace venndev\vosaka\process;

use Generator;
use RuntimeException;
use venndev\vosaka\time\Sleep;
use venndev\vosaka\core\Result;
use venndev\vosaka\core\Constants;
use venndev\vosaka\VOsaka;

final class Process
{
    private bool $running = false;
    private bool $stopped = false;
    private bool $signaled = false;
    private ?int $exitCode = null;
    private ?int $pid = null;
    private mixed $process = null;
    private array $pipes = [];

    public function __construct()
    {
        // Constructor is intentionally left empty.
    }

    public function start(string $cmd, array $descriptorSpec): void
    {
        $this->running = true;

        // Prepare command for different platforms
        $command = $this->prepareCommand($cmd);

        $process = proc_open($command, $descriptorSpec, $pipes);

        if (!is_resource($process)) {
            $this->running = false;
            throw new RuntimeException("Failed to start process: $cmd");
        }
        VOsaka::getLoop()->getGracefulShutdown()->addProcOpen($process, $pipes);

        try {
            $this->process = $process;
            $this->pipes = $pipes;

            // Only set non-blocking mode for pipes that exist and are resources
            foreach ($pipes as $index => $pipe) {
                if (is_resource($pipe)) {
                    stream_set_blocking($pipe, false);

                    // Only set write buffer for writable streams (stdout/stderr)
                    if ($index === 1 || $index === 2) {
                        stream_set_write_buffer($pipe, 0);
                    }
                }
            }

            $this->updateStatus();
            VOsaka::getLoop()
                ->getGracefulShutdown()
                ->addChildProcess($this->pid);

            // Close stdin pipe if it exists and is a resource
            if (isset($pipes[0]) && is_resource($pipes[0])) {
                VOsaka::getLoop()
                    ->getGracefulShutdown()
                    ->removePipe((string) $pipes[0]);
                @fclose($pipes[0]);
            }
        } catch (RuntimeException $e) {
            $this->running = false;

            if (is_resource($process)) {
                proc_close($process);
            }

            throw new RuntimeException("Failed to start process: $cmd", 0, $e);
        } finally {
            VOsaka::getLoop()->getGracefulShutdown()->cleanup();
        }
    }

    private function prepareCommand(string $cmd): string
    {
        if (Stdio::isWindows() && preg_match("/[<>|&]/", $cmd)) {
            // On Windows, wrap command in cmd.exe if it contains shell operators
            return "cmd.exe /c \"$cmd\"";
        }

        return $cmd;
    }

    /**
     * Start the process and handle its output asynchronously
     * @return Result<string>
     */
    public function handle(): Result
    {
        $fn = function (): Generator {
            $output = "";
            $error = "";

            while ($this->running) {
                $this->updateStatus();

                if (!$this->running) {
                    if ($this->stopped || $this->signaled) {
                        yield from $this->terminateProcess()->unwrap();
                        break;
                    }
                }

                $read = [];
                if (isset($this->pipes[1]) && is_resource($this->pipes[1])) {
                    $read[] = $this->pipes[1];
                }
                if (isset($this->pipes[2]) && is_resource($this->pipes[2])) {
                    $read[] = $this->pipes[2];
                }

                if (empty($read)) {
                    yield;
                    continue;
                }

                $write = null;
                $except = null;
                $timeout = 0;
                $stream_result = stream_select(
                    $read,
                    $write,
                    $except,
                    $timeout
                );

                if ($stream_result === false) {
                    throw new RuntimeException("Error during stream select");
                }

                if ($stream_result > 0) {
                    foreach ($read as $stream) {
                        if (!feof($stream)) {
                            $data = stream_get_contents($stream);
                            if ($data !== false && $data !== "") {
                                if ($stream === $this->pipes[1]) {
                                    $output .= $data;
                                } elseif ($stream === $this->pipes[2]) {
                                    $error .= $data;
                                }
                            }
                        }
                    }
                }

                yield;
            }

            $this->collectRemainingOutput($output, $error);

            if ($error !== "") {
                throw new RuntimeException("Process error: $error");
            }

            if (is_resource($this->process)) {
                proc_close($this->process);
                VOsaka::getLoop()->getGracefulShutdown()->cleanup();
            }

            return $output;
        };

        return VOsaka::spawn($fn());
    }

    private function collectRemainingOutput(
        string &$output,
        string &$error
    ): void {
        if (isset($this->pipes[1]) && is_resource($this->pipes[1])) {
            $remainingOutput = stream_get_contents($this->pipes[1]);
            if ($remainingOutput !== false) {
                $output .= $remainingOutput;
            }
            VOsaka::getLoop()
                ->getGracefulShutdown()
                ->removePipe($this->pipes[1]);
            @fclose($this->pipes[1]);
        }

        if (isset($this->pipes[2]) && is_resource($this->pipes[2])) {
            $remainingError = stream_get_contents($this->pipes[2]);
            if ($remainingError !== false) {
                $error .= $remainingError;
            }
            VOsaka::getLoop()
                ->getGracefulShutdown()
                ->removePipe($this->pipes[2]);
            @fclose($this->pipes[2]);
        }
    }

    private function updateStatus(): void
    {
        if (!is_resource($this->process)) {
            $this->running = false;
            return;
        }

        $status = proc_get_status($this->process);
        $this->pid = $status["pid"];
        $this->running = $status["running"];
        $this->stopped = $status["stopped"];
        $this->signaled = $status["signaled"];
        $this->exitCode = $status["exitcode"];
    }

    /**
     * Terminate the process forcefully
     * @return Result<void>
     */
    private function terminateProcess(): Result
    {
        $fn = function (): Generator {
            if (!is_resource($this->process)) {
                return yield;
            }

            if (Stdio::isWindows()) {
                proc_terminate($this->process);

                yield Sleep::c(1);

                $status = proc_get_status($this->process);
                if ($status["running"] && $this->pid) {
                    exec("taskkill /F /PID {$this->pid} 2>NUL");
                }
            } else {
                proc_terminate(
                    $this->process,
                    Constants::getSafeSignal("SIGTERM") ?? Constants::SIGTERM
                );

                yield Sleep::c(1);

                $status = proc_get_status($this->process);
                if ($status["running"]) {
                    proc_terminate(
                        $this->process,
                        Constants::getSafeSignal("SIGKILL") ??
                            Constants::SIGKILL
                    );
                }
            }

            VOsaka::getLoop()->getGracefulShutdown()->cleanup();
            $this->running = false;
        };

        return VOsaka::spawn($fn());
    }

    /**
     * Stop the process gracefully
     * @return Result<void>
     */
    public function stop(): Result
    {
        $fn = function (): Generator {
            $this->running = false;
            yield from $this->terminateProcess()->unwrap();

            if (is_resource($this->process)) {
                proc_close($this->process);
            }

            foreach ($this->pipes as $pipe) {
                if (is_resource($pipe)) {
                    @fclose($pipe);
                }
            }

            $this->pid = null;
            $this->pipes = [];

            VOsaka::getLoop()->getGracefulShutdown()->cleanup();

            yield;
        };

        return VOsaka::spawn($fn());
    }

    public function getPid(): ?int
    {
        return $this->pid;
    }

    public function getExitCode(): ?int
    {
        return $this->exitCode;
    }

    public function isRunning(): bool
    {
        return $this->running;
    }
}
