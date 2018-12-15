<?php

declare(strict_types=1);

namespace Chiron;

use Chiron\Container\Container;
use Chiron\Config\ConfigInterface;
use Chiron\Provider\LoggerServiceProvider;
use Chiron\Provider\RouterServiceProvider;
use Chiron\Provider\ErrorHandlerServiceProvider;
use Chiron\Provider\HttpFactoriesServiceProvider;
use Chiron\Provider\MiddlewaresServiceProvider;
use Chiron\Provider\ServerRequestCreatorServiceProvider;
use Chiron\Provider\ServiceProviderInterface;
use Chiron\Provider\ConfigServiceProvider;
use Chiron\Routing\RouterInterface;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;

// TODO : gérer les alias dans le container => https://github.com/laravel/framework/blob/e0dbd6ab143286d81bedf2b34f8820f3d49ea15f/src/Illuminate/Foundation/Application.php#L1076
// TODO : gestion du "call()" qui retrouve automatiquement les paramétres de la fonction par rapport à ce qu'il y a dans le container :
//https://github.com/Wandu/Framework/blob/master/src/Wandu/DI/Container.php#L279
//https://github.com/illuminate/container/blob/master/Container.php#L569    +   https://github.com/laravel/framework/blob/e0dbd6ab143286d81bedf2b34f8820f3d49ea15f/src/Illuminate/Foundation/Application.php#L795

class Kernel extends Container implements KernelInterface
{
    /**
     * The current globally available kernel (if any).
     *
     * @var static
     */
    protected static $instance;
    //public static $instance;

    /**
     * Indicates if the kernel has "booted".
     *
     * @var bool
     */
    protected $isBooted = false;

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

/*
        $this->set(Kernel::class, $this);
        $this->set(KernelInterface::class, $this);
        $this->set('kernel', $this);
*/

        $this->registerBaseServiceProviders();
    }

/*
    public function __clone()
    {
        $this->set(Kernel::class, $this);
        $this->set(KernelInterface::class, $this);
        $this->set('kernel', $this);
    }
    */

    /**
     * @return KernelInterface
     */
    /*
    public function setAsGlobal() : KernelInterface
    {
        $instance = static::$instance;
        static::$instance = $this;
        return $instance;
    }*/

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
        $this->register(new LoggerServiceProvider());
        $this->register(new RouterServiceProvider());
        $this->register(new MiddlewaresServiceProvider());
        $this->register(new ErrorHandlerServiceProvider());
    }

    /**
     * Set the environment.
     *
     * @param Config $config
     * @return \Clarity\Kernel\Kernel
     */
    public function setEnvironment(string $env): KernelInterface
    {
        $this->set('environment', $env);

        return $this;
    }
    /**
     * Get the config object.
     *
     * @return Config Current configuration
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
    public function setConfig(ConfigInterface $config): KernelInterface
    {
        $this->set('config', $config);

        return $this;
    }
    /**
     * Get the config object.
     *
     * @return Config Current configuration
     */
    public function getConfig(): ConfigInterface
    {
        return $this->get('config');
    }
    /**
     * Set the environment.
     *
     * @param Config $config
     * @return \Clarity\Kernel\Kernel
     */
    public function setLogger(LoggerInterface $logger): KernelInterface
    {
        $this->set('logger', $logger);

        return $this;
    }
    /**
     * Get the config object.
     *
     * @return Config Current configuration
     */
    public function getLogger(): LoggerInterface
    {
        return $this->get('logger');
    }
    /**
     * Get the config object.
     *
     * @return RouterInterface
     */
    public function getRouter(): RouterInterface
    {
        return $this->get('router');
    }

    public function setDebug(bool $debug): KernelInterface
    {
        $settings['app']['debug'] = $debug;
        $this->getConfig()->merge($settings);

        return $this;
    }

    public function getDebug(): bool
    {
        return $this->getConfig()->get('app.debug');
    }

    public function setBasePath(string $basePath): KernelInterface
    {
        $settings['app']['settings']['basePath'] = $basePath;
        $this->getConfig()->merge($settings);

        return $this;
    }

    public function getBasePath(): string
    {
        return $this->getConfig()->get('app.settings.basePath');
    }

    /**
     * Register a service provider with the application.
     *
     * @param  ServiceProviderInterface|string  $provider
     * @return KernelInterface
     */
    // TODO : virer le paramétre force et faire un return void
    public function register($provider): KernelInterface
    {
        $this->resolveProvider($provider);

        // don't process the service if it's already registered
        if (! $this->isProviderRegistered($provider)) {
            $this->registerProvider($provider);

            // If the application has already booted, we will call this boot method on
            // the provider class so it has an opportunity to do its boot logic and
            // will be ready for any usage by this developer's application logic.
            if ($this->isBooted) {
                $this->bootProvider($provider);
            }
        }
        return $this;
    }

    /**
     * Register a service provider with the application.
     *
     * @param  ServiceProviderInterface|string  $provider
     * @return ServiceProviderInterface
     */
    protected function resolveProvider($provider): ServiceProviderInterface
    {
        // If the given "provider" is a string, we will resolve it.
        // This is simply a more convenient way of specifying your service provider classes.
        if (is_string($provider) && class_exists($provider)) {
            $provider = new $provider();
        }

        // TODO : voir si on garder ce throw car de toute facon le typehint va lever une exception.
        if (! $provider instanceof ServiceProviderInterface) {
            throw new InvalidArgumentException(
                sprintf('The provider must be an instance of "%s" or a valid class name.',
                    ServiceProviderInterface::class)
            );
        }

        return $provider;
    }

    protected function registerProvider(ServiceProviderInterface $provider): void
    {
        $provider->register($this);
        // store the registered service
        $this->serviceProviders[get_class($provider)] = $provider;
    }

    protected function isProviderRegistered(ServiceProviderInterface $provider): bool
    {
        // is service already present in the array ? if it's the case, it's already registered.
        return array_key_exists(get_class($provider), $this->serviceProviders);
    }

    /**
     * Boot the application's service providers.
     *
     * @return void
     */
    // TODO : faire un return $this pour cette fonction ????
    public function boot(): KernelInterface
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
     * @param  \Illuminate\Support\ServiceProvider  $provider
     * @return mixed
     */
    protected function bootProvider(ServiceProviderInterface $provider): void
    {
        $provider->boot($this);
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

    /**
     * @return \Wandu\DI\ContainerInterface
     */
    /*
    public function setAsGlobal()
    {
        $instance = static::$instance;
        static::$instance = $this;
        return $instance;
    }*/


    /**
     * Magic method to get or set services using setters/getters
     *
     * @param string $method
     * @param array|null $arguments
     * @return mixed
     * @throws DiException
     */
    /*
    public function __call($method, $arguments = null)
    {
        if (strpos($method, 'get') === 0) {
            $serviceName = substr($method, 3);
            $possibleService = lcfirst($serviceName);
            if (isset($this->_services[$possibleService]) === true) {
                if (empty($arguments) === false) {
                    return $this->get($possibleService, $arguments);
                }
                return $this->get($possibleService);
            }
        }
        if (strpos($method, 'set') === 0) {
            if (isset($arguments[0]) === true) {
                $serviceName = substr($method, 3);
                $this->set(lcfirst($serviceName), $arguments[0]);
                return null;
            }
        }
        throw new DiException('Call to undefined method or service \''.$method."'");
    }*/
}
