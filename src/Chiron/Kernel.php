<?php

declare(strict_types=1);

namespace Chiron;

use Chiron\Config\ConfigInterface;
use Chiron\Container\Container;
use Chiron\Provider\ConfigServiceProvider;
use Chiron\Provider\ErrorHandlerServiceProvider;
use Chiron\Provider\HttpFactoriesServiceProvider;
use Chiron\Provider\LoggerServiceProvider;
use Chiron\Provider\MiddlewaresServiceProvider;
use Chiron\Provider\RouterServiceProvider;
use Chiron\Provider\ServerRequestCreatorServiceProvider;
use Chiron\Provider\ServiceProviderInterface;
use Chiron\Routing\RouterInterface;
use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

//https://github.com/lambirou/babiphp/blob/master/system/Container/ReflectionContainer.php

// TODO : gérer les alias dans le container => https://github.com/laravel/framework/blob/e0dbd6ab143286d81bedf2b34f8820f3d49ea15f/src/Illuminate/Foundation/Application.php#L1076
// TODO : gestion du "call()" qui retrouve automatiquement les paramétres de la fonction par rapport à ce qu'il y a dans le container :
//https://github.com/Wandu/Framework/blob/master/src/Wandu/DI/Container.php#L279
//https://github.com/illuminate/container/blob/master/Container.php#L569    +   https://github.com/laravel/framework/blob/e0dbd6ab143286d81bedf2b34f8820f3d49ea15f/src/Illuminate/Foundation/Application.php#L795

// TODO : ajouter une méthode getEmitter() et setEmitter()
// TODO : renommer la méthode getRequest en getServerRequest()
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

        parent::__construct();

        // TODO : attention si on utilise ce bout de code, il faudra aussi faire une méthode __clone() qui remodifie ces valeurs d'instances. => https://github.com/Wandu/Framework/blob/master/src/Wandu/DI/Container.php#L65
        //$this->instance(Kernel::class, $this);
        //$this->instance(Kernel::class, $this);
        //$this->instance('kernel', $this);

        $this->add(Kernel::class, $this);

        $this->alias(KernelInterface::class, Kernel::class);



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

// TODO : vérifier que cela ne pose pas de problémes si on passe un content à null, si c'est le cas initialiser ce paramétre avec chaine vide.
    public function createResponse(string $content = null,int $statusCode = 200, array $headers = []) :ResponseInterface
    {
        $response = $this->get('responseFactory')->createResponse($statusCode);

        foreach ($headers as $name => $value) {
            $response = $response->withHeader($name, $value);
        }

        // create a new body, because in the PSR spec it's not sure the body in writable in the newly created response.
        //$response->getBody()->write($content);
        $body = $this->get('streamFactory')->createStream($content);
        //$body = $this->get('streamFactory')->createStreamFromFile('php://temp', 'wb+');
        //$body->write($content);

        // TODO : vérifier si il faut faire un rewind ou non sur le body suite au write !!!!
        return $response->withBody($body);
    }

    /**
     * Register all of the base service providers.
     */
    protected function registerBaseServiceProviders()
    {
        $this->register(ConfigServiceProvider::class);
        $this->register(ServerRequestCreatorServiceProvider::class);
        $this->register(HttpFactoriesServiceProvider::class);
        $this->register(LoggerServiceProvider::class);
        $this->register(RouterServiceProvider::class);
        $this->register(MiddlewaresServiceProvider::class);
        $this->register(ErrorHandlerServiceProvider::class);
    }

    /**
     * Set the environment.
     *
     * @param Config $config
     *
     * @return \Clarity\Kernel\Kernel
     */
    public function setEnvironment(string $env): KernelInterface
    {
        $this->add('environment', $env);

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

    public function getRequest(): ServerRequestInterface
    {
        return $this->get('request');
    }

    /**
     * Set the environment.
     *
     * @param Config $config
     *
     * @return \Clarity\Kernel\Kernel
     */
    public function setConfig(ConfigInterface $config): KernelInterface
    {
        $this->add('config', $config);

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
     *
     * @return \Clarity\Kernel\Kernel
     */
    public function setLogger(LoggerInterface $logger): KernelInterface
    {
        $this->add('logger', $logger);

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
     * @param ServiceProviderInterface|string $provider
     *
     * @return KernelInterface
     */
    // TODO : virer le paramétre force et faire un return void
    public function register($provider): KernelInterface
    {
        $provider = $this->resolveProvider($provider);

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
     * @param ServiceProviderInterface|string $provider
     *
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
     */
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
     * @param \Illuminate\Support\ServiceProvider $provider
     *
     * @return mixed
     */
    protected function bootProvider(ServiceProviderInterface $provider): void
    {
        if (method_exists($provider, 'boot')) {
            $this->call([$provider, 'boot']);
        }
        //$provider->boot($this);
    }

    /**
     * Set the globally available instance of the container.
     *
     * @return static
     */
    public static function getInstance(): KernelInterface
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Set the shared instance of the container.
     *
     * @param \Chiron\Container\Container|null $container
     *
     * @return \Chiron\Container\Container|static
     */
    public static function setInstance(KernelInterface $kernel = null)
    {
        return static::$instance = $kernel;
    }

    /*
     * @return \Wandu\DI\ContainerInterface
     */
    /*
    public function setAsGlobal()
    {
        $instance = static::$instance;
        static::$instance = $this;
        return $instance;
    }*/

    /*
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

    /*
     * Aborts the current request by sending a proper HTTP error.
     *
     * @param int    $statusCode The HTTP status code
     * @param string $message    The status message
     * @param array  $headers    An array of HTTP headers
     */
    /*
    public function abort($statusCode, $message = '', array $headers = [])
    {
        throw new HttpException($statusCode, $message, null, $headers);
    }*/






    /**
     * Returns a property of the object or the default value if the property is not set.
     *
     * @param string $key     The name of the property.
     * @param mixed  $default The default value (optional) if none is set.
     *
     * @return mixed The value of the configuration.
     */
    /*
    public function get($key, $default = null)
    {
        return $this->config->get($key, $default);
    }*/


/**
     * Modifies a property of the object, creating it if it does not already exist.
     *
     * @param string $key   The name of the property.
     * @param mixed  $value The value of the property to set (optional).
     *
     * @return mixed Previous value of the property
     */
/*
    public function set($key, $value = null)
    {
        $previous = $this->config->get($key, null);
        $this->config->put($key, $value);
        return $previous;
    }*/


/**
     * Sets the configuration for the application.
     *
     * @param Map $config A structure object holding the configuration.
     *
     * @return AbstractApplication Returns itself to support chaining.
     */
/*
    public function setConfiguration(Map $config)
    {
        $this->config = $config;
        return $this;
    }*/



/**
     * The application configuration object.
     *
     * @var Data
     */
    //protected $config;


/**
     * Class constructor of Application.
     *
     * @param Map|null $config
     */
/*
    public function __construct(Map $config = null)
    {
        $this->config = $config instanceof Map ? $config : new Map();
        $this->init();
    }*/


/**
     * is utilized for reading data from inaccessible members.
     *
     * @param   $name string
     *
     * @return mixed
     */
/*
    public function __get($name)
    {
        $allowNames = [
            'config',
        ];
        if (in_array($name, $allowNames)) {
            return $this->$name;
        }
        throw new \UnexpectedValueException('Property: '.$name.' not found in '.get_called_class());
    }*/
}
