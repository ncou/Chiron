<?php

declare(strict_types=1);

namespace Chiron\Bootload;

use Chiron\Boot\Directories;
use Chiron\Boot\Environment;
use Chiron\Bootload\ServiceProvider\ServiceProviderInterface;
use Chiron\Bootloader\CommandBootloader;
use Chiron\Bootloader\ApplicationBootloader;
use Chiron\Bootloader\DotEnvBootloader;
use Chiron\Bootloader\HttpBootloader;
use Chiron\Bootloader\PublishableCollectionBootloader;
use Chiron\Bootloader\PackageManifestBootloader;
use Chiron\Bootloader\RouteCollectorBootloader;
use Chiron\Bootloader\ViewBootloader;
use Chiron\Config\InjectableConfigMutation;
use Chiron\Config\ConfigManager;
use Chiron\Config\InjectableConfigInterface;
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

// TODO : passer la classe en "final" et passer les propriétes de classe de protected ou public à private !!!!
// TODO : enregistrer aussi les alias : https://github.com/laravel/framework/blob/5.8/src/Illuminate/Foundation/Application.php#L1128
class Configurator // implements SingletonInterface
{
    /**
     * Indicates if the botloaders stack has been "booted".
     *
     * @var bool
     */
    private $isBooted = false;

    /** @var Container */
    private $container;

    /** @var array */
    public $bootloaders = [];

    // TODO : passer un object de type "Container" ou "ContainerInterface" dans ce constructeur plutot que de l'initialiser ici. il faudra reporter la création du container dans la méthode Application::init()
    public function __construct()
    {
        // TODO : vérifier si on ajoute un sharedByDefault=true lors de la création du container (paramétre dans le constructeur)
        $this->container = new Container();
        $this->container->setAsGlobal();

        // add the Configurator class as a shared instance.
        $this->container->share(self::class, $this);
        //$this->container->share(static::class, $this);

        // ### Add default service Providers ###
        //TODO : il y a surement des services à ne pas charger si on est en mode console !!! et inversement il y en a surement à charger uniquement en mode console !!!
        $this->addProvider(new ConfigManagerServiceProvider());
        $this->addProvider(new ServerRequestCreatorServiceProvider());
        $this->addProvider(new HttpFactoriesServiceProvider());
        $this->addProvider(new LoggerServiceProvider());
        $this->addProvider(new MiddlewaresServiceProvider());
        $this->addProvider(new ErrorHandlerServiceProvider());
        $this->addProvider(new RoadRunnerServiceProvider());
        // TODO : à virer c'est un test !!!!!
        $this->addProvider(new \Chiron\Provider\SharedServiceProvider());

        // ### Add default Mutations ###
        $this->container->inflector(InjectableConfigInterface::class, [InjectableConfigMutation::class, 'mutation']);

        // ### Add default Bootloaders ###
        //TODO : il y a surement des services à ne pas charger si on est en mode console !!! et inversement il y en a surement à charger uniquement en mode console !!!
        $this->addBootloader(new DotEnvBootloader());
        // TODO : vérifier si le bootloader ApplicationBootloader ne doit pas être ajouté en dernier !!!!!
        $this->addBootloader(new ApplicationBootloader());

        // TODO : on devrait pouvoir déplacer ces bootloader dans le fichier app.php !!!!
        $this->addBootloader(new CommandBootloader());
        $this->addBootloader(new PublishableCollectionBootloader());
        $this->addBootloader(new PackageManifestBootloader());
    }

    // TODO : il faudrait pas initialiser le container avant de le retourner ??? ou alors cela risque de poser problémes ?????
    // TODO : méthode pas vraiment utile à la limite retourner un ContainerInterface plutot qu'un container, et donc éviter que l'utilisateur ne puisse accéder aux fonction bind/singleton/inflect...etc de l'object Container. L'utilisateur aura seulement accés aux méthodes get/has de base.
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * Register a service provider with the application.
     *
     * @param ServiceProviderInterface $provider
     */
    // TODO : améliorer le code : https://github.com/laravel/framework/blob/5.8/src/Illuminate/Foundation/Application.php#L594
    //public function register($provider)
    public function addProvider(ServiceProviderInterface $provider): void
    {
        $provider->register($this->container);
    }

    /*******************************************************************************
     * Mutations
     ******************************************************************************/

    public function addMutation(): void
    {
        // TODO : à finir de coder
    }

    /*******************************************************************************
     * Alias
     ******************************************************************************/

    // TODO : faire une vérification si la classe passée dans target existe bien ???? et sinon lever une exception ????
    // TODO : cette méthode n'est pas vraiment utile il faudrait la virer !!!!
    public function addAlias(string $class, string $alias): void
    {
        $loader = \Chiron\Facade\AliasLoader::getInstance();
        //$loader->alias('Routing','\Chiron\Facade\Routing');
        $loader->alias($class, $alias);
        $loader->register();
    }

    /*******************************************************************************
     * Command
     ******************************************************************************/

    public function addCommand(string $command): void
    {
        // TODO : finir de coder cette méthode pour ajouter une command dans la console !!!!
    }


    /*******************************************************************************
     * Bootloader
     ******************************************************************************/

    public function addBootloader(BootloaderInterface $bootloader): void
    {
        // if you add a bootloader after the application run(), we execute the bootloader, else we add it to the stack for an execution later.
        if ($this->isBooted) {
            $this->bootload($bootloader);
        } else {
            $this->bootloaders[] = $bootloader;
        }
    }

    /**
     * Boot the application's service providers.
     */
    // TODO : ajouter une sécurité en passant cette méthode en protected, et depuis la classe Application faire une reflection pour la rendre public et appeller cette méthode ??? cela éviterai quelle ne soit appellée manuellement par l'utilisateur avant la méthode run() de l'application ????
    public function boot(): void
    {
        if (! $this->isBooted) {
            $this->isBooted = true;

            foreach ($this->bootloaders as $bootloader) {
                $this->bootload($bootloader);
            }
        }
    }

    /**
     * Boot the given bootloader.
     *
     * @param BootloaderInterface $provider
     *
     * @return mixed
     */
    // TODO : déporter le invoker de la méthode boot dans une classe AbstractBootloader, on devrait seulement executer la méthode boot() avec en paramétre le container !!!
    protected function bootload(BootloaderInterface $provider): void
    {
        if (method_exists($provider, 'boot')) {
            // TODO : améliorer le code pour créer une seule fois l'objet Invoker ca consommera moins de mémoire (surtout qu'on a beaucoup de bootloader à executer)
            (new Invoker($this->container))->invoke([$provider, 'boot']);
        }
    }
}
