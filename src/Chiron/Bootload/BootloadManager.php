<?php

declare(strict_types=1);

namespace Chiron\Bootload;

use Chiron\Invoker\Invoker;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;

//https://github.com/laravel/lumen-framework/blob/6.x/src/Application.php#L222
final class BootloadManager
{
    /**
     * Indicates if the kernel has "booted".
     *
     * @var bool
     */
    private $isBooted = false;

    /** @var array */
    private $serviceProviders = [];

    private $container;

    /**
     * Invoker constructor.
     *
     * @param $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function bootloader($provider): self
    {
        $provider = $this->resolveProvider($provider);

        // don't process the service if it's already registered
        if (! $this->isProviderRegistered($provider)) {
            // store the registered service
            $this->serviceProviders[get_class($provider)] = $provider;
            $this->bootProvider($provider);
        }

        return $this;
    }

    /**
     * Register a service provider with the application.
     *
     * @param BootloaderInterface|string $provider
     *
     * @return BootloaderInterface
     */
    protected function resolveProvider($provider): BootloaderInterface
    {
        // If the given "provider" is a string, we will resolve it.
        // This is simply a more convenient way of specifying your service provider classes.
        if (is_string($provider) && class_exists($provider)) {
            $provider = new $provider();
        }

        // TODO : voir si on garder ce throw car de toute facon le typehint va lever une exception.
        if (! $provider instanceof BootloaderInterface) {
            throw new InvalidArgumentException(
                sprintf(
                    'The provider must be an instance of "%s" or a valid class name.',
                    BootloaderInterface::class
                )
            );
        }

        return $provider;
    }

    protected function isProviderRegistered(BootloaderInterface $provider): bool
    {
        // is service already present in the array ? if it's the case, it's already registered.
        return array_key_exists(get_class($provider), $this->serviceProviders);
    }

    /**
     * Boot the application's service providers.
     */
    public function boot(): self
    {
        if (! $this->isBooted) {
            foreach ($this->serviceProviders as $provider) {
                $this->bootProvider($provider);
            }
            $this->isBooted = true;
        }

        return $this;
    }

    /**
     * Boot the given service provider.
     *
     * @param BootloaderInterface $provider
     *
     * @return mixed
     */
    protected function bootProvider(BootloaderInterface $provider): void
    {
        if (method_exists($provider, 'boot')) {
            //$this->call([$provider, 'boot']);
            (new Invoker($this->container))->invoke([$provider, 'boot']);
        }
    }
}