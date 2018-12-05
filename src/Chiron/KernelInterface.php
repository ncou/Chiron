<?php

declare(strict_types=1);

namespace Chiron;

use Chiron\Container\Container;
use Chiron\Config\Config;
use Chiron\Provider\ApplicationServiceProvider;
use Chiron\Provider\ErrorHandlerServiceProvider;
use Chiron\Provider\HttpFactoriesServiceProvider;
use Chiron\Provider\MiddlewaresServiceProvider;
use Chiron\Provider\ServerRequestCreatorServiceProvider;

interface KernelInterface
{

    /**
     * Set the environment.
     *
     * @param string $env
     * @return \Clarity\Kernel\Kernel
     */
    public function setEnvironment(string $env): KernelInterface;

    /**
     * Get the environment.
     *
     * @return string Current environment
     */
    public function getEnvironment(): string;


    /**
     * Set the environment.
     *
     * @param Config $config
     * @return \Clarity\Kernel\Kernel
     */
    public function setConfig(Config $config): KernelInterface;

    /**
     * Get the config object.
     *
     * @return Config Current configuration
     */
    public function getConfig(): Config;

    /**
     * Register a service provider with the application.
     *
     * @param  \Illuminate\Support\ServiceProvider|string  $provider
     * @param  bool   $force
     * @return \Illuminate\Support\ServiceProvider
     */
    public function register($provider, $force = false);


    /**
     * Boot the application's service providers.
     *
     * @return void
     */
    public function boot(): KernelInterface;

}
