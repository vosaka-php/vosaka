<?php

declare(strict_types=1);

namespace venndev\vosaka\net\dns\exceptions;

use Throwable;

/**
 * DNS Configuration Exception
 *
 * Thrown when DNS client configuration is invalid or contains errors.
 */
class DNSConfigurationException extends DNSException
{
    /**
     * Configuration parameter that caused the error
     * @var string|null
     */
    private ?string $configParameter = null;

    /**
     * Invalid configuration value
     * @var mixed
     */
    private mixed $configValue = null;

    /**
     * Create DNS configuration exception
     *
     * @param string $message Exception message
     * @param string|null $configParameter Configuration parameter name
     * @param mixed $configValue Invalid configuration value
     * @param int $code Exception code
     * @param Throwable|null $previous Previous exception
     */
    public function __construct(
        string $message,
        ?string $configParameter = null,
        mixed $configValue = null,
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->configParameter = $configParameter;
        $this->configValue = $configValue;
    }

    /**
     * Get the configuration parameter that caused the error
     *
     * @return string|null
     */
    public function getConfigParameter(): ?string
    {
        return $this->configParameter;
    }

    /**
     * Get the invalid configuration value
     *
     * @return mixed
     */
    public function getConfigValue(): mixed
    {
        return $this->configValue;
    }
}
