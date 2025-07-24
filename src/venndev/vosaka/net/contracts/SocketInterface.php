<?php

declare(strict_types=1);

namespace venndev\vosaka\net\contracts;

/**
 * Low-level socket interface
 */
interface SocketInterface
{
    /**
     * Get the underlying socket resource
     * 
     * @return resource
     */
    public function getResource();

    /**
     * Set socket option
     * 
     * @param int $level
     * @param int $option
     * @param mixed $value
     */
    public function setOption(int $level, int $option, mixed $value): void;

    /**
     * Get socket option
     * 
     * @param int $level
     * @param int $option
     * @return mixed
     */
    public function getOption(int $level, int $option): mixed;

    /**
     * Set blocking mode
     */
    public function setBlocking(bool $blocking): void;

    /**
     * Shutdown socket
     * 
     * @param int $how 0=read, 1=write, 2=both
     */
    public function shutdown(int $how = 2): void;
}
