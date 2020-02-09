<?php

declare(strict_types=1);

namespace Chiron\Bootload\ServiceProvider;

use Chiron\Container\Container;
use Chiron\Container\BindingInterface;

/**
 * Defines the interface for a Service Provider.
 */
interface ServiceProviderInterface
{
    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param BindingInterface $container A container instance
     */
    // TODO : passer en paramétre pas un Container mais un BindingInterface (pour éviter de pouvoir faire un ->get dans la méthode register !!!!)
    public function register(BindingInterface $container): void;

    /**
     * Boots services on the given container.
     *
     * @param Container $container A container instance
     */
    // TODO : à virer !!!!
    //public function boot(Container $container): void;
}
