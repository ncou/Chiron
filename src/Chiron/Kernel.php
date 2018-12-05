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
use Chiron\Provider\ServiceProviderInterface;
use Chiron\Provider\ConfigServiceProvider;

// TODO : gérer les alias dans le container => https://github.com/laravel/framework/blob/e0dbd6ab143286d81bedf2b34f8820f3d49ea15f/src/Illuminate/Foundation/Application.php#L1076

class Kernel extends Container implements KernelInterface
{
    /**
     * The current globally available kernel (if any).
     *
     * @var static
     */
    protected static $instance;
    /**
     * Indicates if the kernel has "booted".
     *
     * @var bool
     */
    protected $booted = false;

    /**
     * All of the registered service providers.
     *
     * @var array
     */
    protected $serviceProviders = [];


// TODO : ajouter la possibiilité de passer directement un objet Config dans le constructeur, si il est null on initialise un nouveau config.
    public function __construct()
    {
        static::setInstance($this);

        $this->registerBaseServiceProviders();
    }

    /**
     * Set the environment.
     *
     * @param string $env
     * @return \Clarity\Kernel\Kernel
     */
    public function setEnvironment(string $env): KernelInterface
    {
        $this->set('environment', $env);

        return $this;
    }
    /**
     * Get the environment.
     *
     * @return string Current environment
     */
    public function getEnvironment(): string
    {
        return $this->get('environment');
    }

    /**
     * Set the environment.
     *
     * @param Config $config
     * @return \Clarity\Kernel\Kernel
     */
    public function setConfig(Config $config): KernelInterface
    {
        $this->set('config', $config);

        return $this;
    }
    /**
     * Get the config object.
     *
     * @return Config Current configuration
     */
    public function getConfig(): Config
    {
        return $this->get('config');
    }

    /**
     * Register all of the base service providers.
     *
     * @return void
     */
    protected function registerBaseServiceProviders()
    {
        $this->register(new ConfigServiceProvider());
        $this->register(new ServerRequestCreatorServiceProvider());
        $this->register(new HttpFactoriesServiceProvider());
        $this->register(new ApplicationServiceProvider());
        $this->register(new MiddlewaresServiceProvider());
        $this->register(new ErrorHandlerServiceProvider());
    }

    /**
     * Register a service provider with the application.
     *
     * @param  \Illuminate\Support\ServiceProvider|string  $provider
     * @param  bool   $force
     * @return \Illuminate\Support\ServiceProvider
     */
    // TODO : virer le paramétre force et faire un return void
    public function register($provider, $force = false)
    {
        //TODO : faire une vérif que $provider est une string ou une instance de Chiron\Provider\ServiceProviderInterface, sinon throw an exception

        if (($registered = $this->getProvider($provider)) && ! $force) {
            return $registered;
        }
        // If the given "provider" is a string, we will resolve it, passing in the
        // application instance automatically for the developer. This is simply
        // a more convenient way of specifying your service provider classes.
        if (is_string($provider)) {
            $provider = $this->resolveProvider($provider);
        }
        if (method_exists($provider, 'register')) {
            $provider->register($this);
        }

        $this->markAsRegistered($provider);
        // If the application has already booted, we will call this boot method on
        // the provider class so it has an opportunity to do its boot logic and
        // will be ready for any usage by this developer's application logic.
        if ($this->booted) {
            $this->bootProvider($provider);
        }
        return $provider;
    }

/*
    public function boot()
    {
        if (!$this->isBooted) {
            foreach ($this->providers as $provider) {
                $provider->boot($this);
            }
            $this->isBooted = true;
        }
        return $this;
    }
*/

    /**
     * Boot the application's service providers.
     *
     * @return void
     */
    // TODO : faire un return $this pour cette fonction ????
    public function boot(): KernelInterface
    {
        if (! $this->booted) {
            array_walk($this->serviceProviders, function ($p) {
                $this->bootProvider($p);
            });

            $this->booted = true;
        }

        return $this;
    }
    /**
     * Boot the given service provider.
     *
     * @param  \Illuminate\Support\ServiceProvider  $provider
     * @return mixed
     */
    // TODO : forcer le type de retour en void
    protected function bootProvider(ServiceProviderInterface $provider)
    {
        if (method_exists($provider, 'boot')) {
            //return $this->call([$provider, 'boot']);
            $provider->boot($this);
        }
    }


    /**
     * Get the registered service provider instance if it exists.
     *
     * @param  \Illuminate\Support\ServiceProvider|string  $provider
     * @return \Illuminate\Support\ServiceProvider|null
     */
    public function getProvider($provider): ?ServiceProviderInterface
    {
        return array_values($this->getProviders($provider))[0] ?? null;
    }
    /**
     * Get the registered service provider instances if any exist.
     *
     * @param  \Illuminate\Support\ServiceProvider|string  $provider
     * @return array
     */
    public function getProviders($provider)
    {
        $name = is_string($provider) ? $provider : get_class($provider);

        return array_filter($this->serviceProviders, function ($value) use ($name) {
            return $value instanceof $name;
        }, ARRAY_FILTER_USE_BOTH);
    }
    /**
     * Mark the given provider as registered.
     *
     * @param  \Illuminate\Support\ServiceProvider  $provider
     * @return void
     */
    protected function markAsRegistered($provider)
    {
        $this->serviceProviders[] = $provider;
    }

    /**
     * Resolve a service provider instance from the class name.
     *
     * @param  string  $provider
     * @return \Chiron\Provider\ServiceProviderInterface
     */
    public function resolveProvider($provider): ServiceProviderInterface
    {
        return new $provider($this);
    }

    /**
     * Set the globally available instance of the container.
     *
     * @return static
     */
    public static function getInstance() : KernelInterface
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }
        return static::$instance;
    }

    /**
     * Set the shared instance of the container.
     *
     * @param  \Chiron\Container\Container|null  $container
     * @return \Chiron\Container\Container|static
     */
    public static function setInstance(KernelInterface $kernel = null)
    {
        return static::$instance = $kernel;
    }
}
