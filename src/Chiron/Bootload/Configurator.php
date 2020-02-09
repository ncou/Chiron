<?php

declare(strict_types=1);

namespace Chiron\Bootload;

use Chiron\Config\ConfigInterface;
use Chiron\Config\ConfigManager;
use Chiron\Container\Container;
use Chiron\Http\Emitter\ResponseEmitter;
use Chiron\Pipe\PipelineBuilder;
use Chiron\Provider\ConfigManagerServiceProvider;
use Chiron\Provider\DotEnvServiceProvider;
use Chiron\Provider\ErrorHandlerServiceProvider;
use Chiron\Provider\HttpFactoriesServiceProvider;
use Chiron\Provider\LoggerServiceProvider;
use Chiron\Provider\MiddlewaresServiceProvider;
use Chiron\Provider\RouterServiceProvider;
use Chiron\Provider\ServerRequestCreatorServiceProvider;
use Chiron\Router\RouterInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Chiron\Boot\DirectoriesInterface;
use Chiron\Boot\Directories;
use Chiron\Boot\EnvironmentInterface;
use Chiron\Boot\Environment;
use Chiron\Boot\BootloadManager;
use Chiron\Bootload\ServiceProvider\ServiceProviderInterface;
use InvalidArgumentException;
use Chiron\Views\PhpRendererBootable;

use Chiron\Config\InjectableInterface;
use Chiron\Config\ConfigInflector;

class Configurator
{
    /**
     * The base path of the application installation.
     *
     * @var string
     */
    private $basePath;

    /** @var Container */
    private $container;

    /** @var array */
    private $services = [];

    /** @var array */
    protected $serviceProviders = [];

    public function __construct(string $basePath = null)
    {
        // TODO : ajouter un rtrim($basepath, '\/') ?????
        $this->basePath = $basePath;
    }

    /**
     * Add multiple definitions at once.
     *
     * @param array $config definitions indexed by their ids
     */
    public function addServices(array $config): void
    {
        $this->services = $config;
    }

    public function createContainer(): Container
    {
        // TODO : vérifier si on ajoute un sharedByDefault=true lors de la création du container
        $this->container = new Container();
        $this->container->setAsGlobal();
        //Container::setInstance($this->container);

        $this->registerBaseServiceProviders();

        foreach ($this->services as $id => $definition) {
            $this->container->add($id, $definition);
        }

        $this->inflector();

        $this->boot();

        return $this->container;
    }

    public function inflector()
    {
        // TODO : éviter de passer un $container en paramétre de la classe ConfigInflector mais lui passer un object ConfigManager !!!
        $this->container->inflector(InjectableInterface::class, new ConfigInflector($this->container));
        //$this->container->inflector(InjectableInterface::class, $this->container->build(ConfigInflector::class));
    }

    public function boot(): void
    {
        $bootload = new BootloadManager($this->container);

        $bootload->register(DotEnvServiceProvider::class);
        //$bootload->register(ConfigManagerServiceProvider::class);

        // TODO : à virer c'est un test !!!!!
        $bootload->register(PhpRendererBootable::class);
        // TODO : à virer c'est un test !!!!!
        $bootload->register(\Providers\LoggerServiceProvider::class);
        // TODO : à virer c'est un test !!!!!
        $bootload->register(\Providers\LoadRoutesServiceProvider::class);
    }

    /**
     * Normalizes directory list and adds all required aliases.
     *
     * @param array $directories
     * @return array
     */
    protected function mapDirectories(array $directories): array
    {
        if (!isset($directories['root'])) {
            //throw new LogicException("Missing required directory 'root'.");
            $directories['root'] = $this->basePath();
        }

        if (!isset($directories['app'])) {
            $directories['app'] = $directories['root'] . '/app/';
        }

        return array_merge([
            // public root
            'public'    => $directories['root'] . '/public/',
            // vendor libraries
            'vendor'    => $directories['root'] . '/vendor/',
            // templates libraries
            'templates'    => $directories['root'] . '/templates/',
            // data directories
            'runtime'   => $directories['root'] . '/runtime/',
            'cache'     => $directories['root'] . '/runtime/cache/',
            // application directories
            //'config'    => $directories['app'] . '/config/',
            'config'    => $directories['root'] . '/config/',
            'resources' => $directories['app'] . '/resources/',
        ], $directories);
    }

    /**
     * Get the base path for the application.
     *
     * @param  string|null  $path
     * @return string
     */
    // TODO : renommer toutes les variables et fonctions de basePath en rootPath !!!!
    public function basePath(?string $path = null): string
    {
        if (isset($this->basePath)) {
            return $this->basePath.($path ? '/'.$path : $path);
        }
        //if ($this->runningInConsole()) {
        //    $this->basePath = getcwd();
        //} else {
            $this->basePath = realpath(getcwd().'/../');
        //}
        return $this->basePath($path);
    }

    /**
     * Register all of the base service providers.
     */
    // TODO : enregistrer aussi les alias : https://github.com/laravel/framework/blob/5.8/src/Illuminate/Foundation/Application.php#L1128
    protected function registerBaseServiceProviders()
    {
        // TODO : à déporter dans un serviceprovider cad dans un fichier séparé !!!!
        // TODO : virer la classe DirectoriesInterface ???? elle ne semble pas servir à grand chose... ???? non ????
        $directories = [];
        $this->container->share(Directories::class, new Directories($this->mapDirectories($directories)));
        $this->container->alias(DirectoriesInterface::class, Directories::class);

        $this->container->share(EnvironmentInterface::class, Environment::class);

        //$this->register(DotEnvServiceProvider::class);
        $this->register(ConfigManagerServiceProvider::class);
        $this->register(ServerRequestCreatorServiceProvider::class);
        $this->register(HttpFactoriesServiceProvider::class);
        $this->register(LoggerServiceProvider::class);
        $this->register(RouterServiceProvider::class);
        $this->register(MiddlewaresServiceProvider::class);
        $this->register(ErrorHandlerServiceProvider::class);

        // TODO : à virer c'est un test !!!!
        $this->register(\Chiron\Views\Provider\PhpRendererServiceProvider::class);
        // TODO : à virer c'est un test !!!!!
        $this->register(\Providers\DatabaseServiceProvider::class);
    }

    /*******************************************************************************
     * Service Provider
     ******************************************************************************/

    /**
     * Register a service provider with the application.
     *
     * @param ServiceProviderInterface|string $provider
     *
     * @return self
     */
    // TODO : améliorer le code : https://github.com/laravel/framework/blob/5.8/src/Illuminate/Foundation/Application.php#L594
    public function register($provider)//: self
    {
        $provider = $this->resolveProvider($provider);

        // don't process the service if it's already registered
        if (! $this->isProviderRegistered($provider)) {
            $this->registerProvider($provider);
        }

        //return $this;
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

    protected function isProviderRegistered(ServiceProviderInterface $provider): bool
    {
        // is service already present in the array ? if it's the case, it's already registered.
        return array_key_exists(get_class($provider), $this->serviceProviders);
    }

    protected function registerProvider(ServiceProviderInterface $provider): void
    {
        $provider->register($this->container);
        // store the registered service
        $this->serviceProviders[get_class($provider)] = $provider;
    }

}
