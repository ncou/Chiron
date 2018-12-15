<?php

declare(strict_types=1);

namespace Chiron;

use ArrayAccess;
use Chiron\Container\Container;
use Psr\Container\ContainerInterface;
use Chiron\Config\ConfigInterface;
use Chiron\Provider\ApplicationServiceProvider;
use Chiron\Provider\ErrorHandlerServiceProvider;
use Chiron\Provider\HttpFactoriesServiceProvider;
use Chiron\Provider\MiddlewaresServiceProvider;
use Chiron\Provider\ServerRequestCreatorServiceProvider;

// TODO : finir d'ajouter toutes les méthodes du kernel (getInstance/setInstance...etc)
// TODO : vérifier si on a vraiment besoin de l'interface ArrayAccess
interface KernelInterface extends ContainerInterface, ArrayAccess
{
    /**
     * Set the config object.
     *
     * @param ConfigInterface $config
     * @return KernelInterface
     */
    public function setConfig(ConfigInterface $config): KernelInterface;

    /**
     * Get the config object.
     *
     * @return ConfigInterface
     */
    public function getConfig(): ConfigInterface;

    /**
     * Set the environment.
     *
     * @param string $env
     * @return KernelInterface
     */
    public function setEnvironment(string $env): KernelInterface;
    /**
     * Get the environment value.
     *
     * @return string
     */
    public function getEnvironment(): string;

    /**
     * Register a service provider with the application.
     *
     * @param  ServiceProviderInterface|string  $provider
     * @return KernelInterface
     */
    public function register($provider): KernelInterface;

    /**
     * Boot the application's service providers.
     *
     * @return void
     */
    public function boot(): KernelInterface;

}
