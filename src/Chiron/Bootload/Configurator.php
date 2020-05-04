<?php

declare(strict_types=1);

namespace Chiron\Bootload;

use Chiron\Boot\Directories;
use Chiron\Boot\DirectoriesInterface;
use Chiron\Boot\Environment;
use Chiron\Boot\EnvironmentInterface;
use Chiron\Bootload\ServiceProvider\ServiceProviderInterface;
use Chiron\Bootloader\CommandBootloader;
use Chiron\Bootloader\DispatcherBootloader;
use Chiron\Bootloader\DotEnvBootloader;
use Chiron\Bootloader\HttpBootloader;
use Chiron\Bootloader\PublishableCollectionBootloader;
use Chiron\Bootloader\PackageManifestBootloader;
use Chiron\Bootloader\RouteCollectorBootloader;
use Chiron\Bootloader\ViewBootloader;
use Chiron\Config\ConfigInflector;
use Chiron\Config\ConfigManager;
use Chiron\Config\InjectableInterface;
use Chiron\Container\Container;
use Chiron\Provider\ConfigManagerServiceProvider;
use Chiron\Provider\ErrorHandlerServiceProvider;
use Chiron\Provider\HttpFactoriesServiceProvider;
use Chiron\Provider\LoggerServiceProvider;
use Chiron\Provider\MiddlewaresServiceProvider;
use Chiron\Provider\RoadRunnerServiceProvider;
use Chiron\Provider\ServerRequestCreatorServiceProvider;
use Chiron\Application;
use Chiron\Invoker\Invoker;
use InvalidArgumentException;

class Configurator
{
    /**
     * Indicates if the botloader has been "booted".
     *
     * @var bool
     */
    private $isBooted = false;

    /**
     * The base path of the application installation.
     *
     * @var string
     */
    // TODO : renommer en rootPath !!!!!
    private $basePath;

    /** @var Container */
    private $container;

    /** @var array */
    protected $serviceProviders = [];

    /** @var array */
    protected $bootloaders = [];

    public function __construct(string $basePath = null)
    {
        // TODO : code à améliorer pour l'instant c'est pour faire des tests avec des erreurs en mode "console"
        // TODO : il faudra mettre la lancement du error handler (le call à la fonction register) en tout début d'execution du code, pour gérer aussi les erreurs lorsqu'on enregistre les services providers par exemple. il faudra donc déplacer le code dans le composer Configurator.
        //if (PHP_SAPI === 'cli') {
            $error = new \Chiron\ErrorHandler\RegisterErrorHandler();
            $error->register();
        //}


        // TODO : ajouter un rtrim($basepath, '\/') ?????
        $this->basePath = $basePath;

        // TODO : vérifier si on ajoute un sharedByDefault=true lors de la création du container
        $this->container = new Container();
        $this->container->setAsGlobal();

        //$this->container->share(Configurator2::class, $this);
        $this->container->share(self::class, $this);
        //$this->container->bindSingleton(static::class, $this);


        $this->addBaseBootloaders();
    }

    /**
     * Get the base path for the application.
     *
     * @param string|null $path
     *
     * @return string
     */
    // TODO : renommer toutes les variables et fonctions de basePath en rootPath !!!!
    public function basePath(?string $path = null): string
    {
        if (isset($this->basePath)) {
            return $this->basePath . ($path ? '/' . $path : $path);
        }

        if (PHP_SAPI === 'cli') {
            $this->basePath = getcwd();
        } else {
            $this->basePath = realpath(getcwd() . '/../');
        }

        return $this->basePath($path);
    }

    // TODO : il faudrait pas initialiser le container avant de le retourner ??? ou alors cela risque de poser problémes ?????
    public function getContainer(): Container
    {
        return $this->container;
    }

    // TODO : permettre de passer en paramétre un tableau avec les directories à utiliser ????
    public function getApplication(): Application
    {
        $this->initContainer();

        return $this->getContainer()->get(Application::class);
    }

    public function initContainer()
    {
        // TODO : à déporter dans un serviceprovider cad dans un fichier séparé !!!!
        // TODO : virer la classe DirectoriesInterface ???? elle ne semble pas servir à grand chose... ???? non ????
        $directories = [];
        $this->container->share(Directories::class, new Directories($this->mapDirectories($directories)));
        $this->container->alias(DirectoriesInterface::class, Directories::class);

        $this->container->share(EnvironmentInterface::class, Environment::class);


        $this->registerBaseServiceProviders();

        // TODO : code à améliorer c'est pas terrible. et c'est dupliqué avec le code dans le init !!!!
        // register all the "default" providers
        foreach ($this->serviceProviders as $provider) {
            $provider->register($this->container);
        }


        $this->inflector();
    }

    /**
     * Register all of the base service providers.
     */
    // TODO : enregistrer aussi les alias : https://github.com/laravel/framework/blob/5.8/src/Illuminate/Foundation/Application.php#L1128
    protected function registerBaseServiceProviders()
    {
        //TODO : il y a surement des services à ne pas charger si on est en mode console !!! et inversement il y en a surement à charger uniquement en mode console !!!
        $this->addProvider(ConfigManagerServiceProvider::class);
        $this->addProvider(ServerRequestCreatorServiceProvider::class);
        $this->addProvider(HttpFactoriesServiceProvider::class);
        $this->addProvider(LoggerServiceProvider::class);
        $this->addProvider(MiddlewaresServiceProvider::class);
        $this->addProvider(ErrorHandlerServiceProvider::class);
        $this->addProvider(RoadRunnerServiceProvider::class);
    }

    /**
     * Normalizes directory list and adds all required aliases.
     *
     * @param array $directories
     *
     * @return array
     */
    protected function mapDirectories(array $directories): array
    {
        if (! isset($directories['root'])) {
            //throw new LogicException("Missing required directory 'root'.");

            //die($this->basePath());

            $directories['root'] = $this->basePath();
        }

        if (! isset($directories['app'])) {
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

    protected function inflector()
    {
        // TODO : éviter de passer un $container en paramétre de la classe ConfigInflector mais lui passer un object ConfigManager !!!
        $this->container->inflector(InjectableInterface::class, new ConfigInflector($this->container));
        //$this->container->inflector(InjectableInterface::class, $this->container->build(ConfigInflector::class));
    }

    /*******************************************************************************
     * Alias
     ******************************************************************************/

    // TODO : faire une vérification si la classe passée dans target existe bien ???? et sinon lever une exception ????
    public function addAlias(string $class, string $alias)
    {
        $loader = \Chiron\Facade\AliasLoader::getInstance();
        //$loader->alias('Routing','\Chiron\Facade\Routing');
        $loader->alias($class, $alias);
        $loader->register();
    }

    /*******************************************************************************
     * Command
     ******************************************************************************/

    public function addCommand(string $command)
    {
        // TODO : finir de coder cette méthode pour ajouter une command dans la console !!!!
    }


    /*******************************************************************************
     * Bootloader
     ******************************************************************************/

    private function addBaseBootloaders()
    {
        //TODO : il y a surement des services à ne pas charger si on est en mode console !!! et inversement il y en a surement à charger uniquement en mode console !!!
        $this->addBootloader(DotEnvBootloader::class);
        $this->addBootloader(DispatcherBootloader::class);
        $this->addBootloader(CommandBootloader::class);
        $this->addBootloader(PublishableCollectionBootloader::class);
        $this->addBootloader(PackageManifestBootloader::class);
    }

    public function addBootloader($bootloader)
    {
        $bootloader = $this->resolveBootloader($bootloader);

        // if you add a bootloader after the application run(), we execute the bootloader, else we add it to the stack for an execution later.
        if ($this->isBooted) {
            $this->bootLoader($bootloader);
        } else {
            $this->bootloaders[] = $bootloader;
        }
    }

    /**
     * Resolve a bootloader.
     *
     * @param BootloaderInterface|string $provider
     *
     * @return BootloaderInterface
     */
    protected function resolveBootloader($provider): BootloaderInterface
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
                    'The bootloader must be an instance of "%s" or a valid class name.',
                    BootloaderInterface::class
                )
            );
        }

        return $provider;
    }

    public function init(): void
    {
        // register all the providers
        foreach ($this->serviceProviders as $provider) {
            $provider->register($this->container);
        }

        // boot all the bootloaders
        $this->boot();
    }


    /**
     * Boot the application's service providers.
     */
    // TODO : ajouter une sécurité en passant cette méthode en protected, et depuis la classe Application faire une reflection pour la rendre public et appeller cette méthode ??? cela éviterai quelle ne soit appellée manuellement par l'utilisateur avant la méthode run() de l'application ????
    private function boot(): void
    {
        if (! $this->isBooted) {
            foreach ($this->bootloaders as $bootloader) {
                $this->bootLoader($bootloader);
            }
            $this->isBooted = true;
        }
    }

    /**
     * Boot the given bootloader.
     *
     * @param BootloaderInterface $provider
     *
     * @return mixed
     */
    protected function bootLoader(BootloaderInterface $provider): void
    {
        if (method_exists($provider, 'boot')) {
            // TODO : améliorer le code pour créer une seule fois l'objet Invoker ca consommera moins de mémoire (surtout qu'on a beaucoup de bootloader à executer)
            (new Invoker($this->container))->invoke([$provider, 'boot']);
        }
    }

    /*******************************************************************************
     * Service Provider
     ******************************************************************************/

    /**
     * Register a service provider with the application.
     *
     * @param ServiceProviderInterface|string $provider
     */
    // TODO : améliorer le code : https://github.com/laravel/framework/blob/5.8/src/Illuminate/Foundation/Application.php#L594
    //public function register($provider)
    public function addProvider($provider)
    {
        $provider = $this->resolveProvider($provider);

        // don't process the service if it's already registered
        if (! $this->isProviderRegistered($provider)) {
            $this->registerProvider($provider);
        }
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
                sprintf(
                    'The provider must be an instance of "%s" or a valid class name.',
                    ServiceProviderInterface::class
                )
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
        // store the registered service
        $this->serviceProviders[get_class($provider)] = $provider;
    }
}
