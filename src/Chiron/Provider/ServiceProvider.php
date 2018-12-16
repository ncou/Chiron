<?php

declare(strict_types=1);

namespace Chiron\Provider;

use Chiron\Container\Container;
use Chiron\KernelInterface;

/**
 * Defines the interface for a Service Provider.
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * Boots services on the given container.
     *
     * @param KernelInterface $kernel A kernel instance
     */
    public function boot(KernelInterface $kernel): void
    {
    }

    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param KernelInterface $kernel A kernel instance
     */
    public function register(KernelInterface $kernel): void
    {
    }
}
