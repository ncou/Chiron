<?php

declare(strict_types=1);

namespace Chiron\Bootload;

use Chiron\Application;
use Chiron\Bootload\ServiceProvider\ServiceProviderInterface;
use Chiron\Bootloader\ApplicationBootloader;
use Chiron\Bootloader\CommandBootloader;
use Chiron\Bootloader\DotEnvBootloader;
use Chiron\Bootloader\PackageManifestBootloader;
use Chiron\Bootloader\PublishableCollectionBootloader;
use Chiron\Config\InjectableConfigInterface;
use Chiron\Config\InjectableConfigMutation;
use Chiron\Container\Container;
use Chiron\Provider\ConfigManagerServiceProvider;
use Chiron\Provider\ErrorHandlerServiceProvider;
use Chiron\Provider\HttpFactoriesServiceProvider;
use Chiron\Provider\LoggerServiceProvider;
use Chiron\Provider\MiddlewaresServiceProvider;
use Chiron\Provider\RoadRunnerServiceProvider;
use Chiron\Provider\ServerRequestCreatorServiceProvider;

// TODO : éviter de passer deux fois les mémes services (provider ou bootloader) on ne chargera que la 1er fois. Ce cas peut se produire si l'utilisateur utilise le module autodiscovery + un ajout manuel des services.
// TODO : passer la classe en "final" et passer les propriétes de classe de protected ou public à private !!!!
// TODO : enregistrer aussi les alias : https://github.com/laravel/framework/blob/5.8/src/Illuminate/Foundation/Application.php#L1128
// TODO : renommer cette classe en "ServiceManager" ????
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

    // TODO : passer un object de type "Container" ou "ContainerInterface" dans ce constructeur plutot que de l'initialiser ici. il faudra reporter la création du container dans la méthode Application::init() ou dans un ContainerFactory::create() qui initialisera aussi les services de base à injecter dans le container (c'est à dire le code qui est actuellement dans ce constructeur ci dessous)
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

        // TODO : théoriquement tout cela peut être déplacé dans le core.php
        /*
        $this->addProvider(new ServerRequestCreatorServiceProvider());
        $this->addProvider(new HttpFactoriesServiceProvider());
        $this->addProvider(new LoggerServiceProvider());
        $this->addProvider(new MiddlewaresServiceProvider());
        $this->addProvider(new ErrorHandlerServiceProvider());
        $this->addProvider(new RoadRunnerServiceProvider());
        */

        // TODO : à remplacer par l'utilisation de l'interface Container\SingletonInterface::class
        $this->addProvider(new \Chiron\Provider\SharedServiceProvider());

        // ### Add default Mutations ###
        $this->container->inflector(InjectableConfigInterface::class, [InjectableConfigMutation::class, 'mutation']);

        // ### Add default Bootloaders ###
        //TODO : il y a surement des services à ne pas charger si on est en mode console !!! et inversement il y en a surement à charger uniquement en mode console !!!
        $this->addBootloader(new DotEnvBootloader());
        // TODO : vérifier si le bootloader ApplicationBootloader ne doit pas être ajouté en dernier !!!!!
        $this->addBootloader(new ApplicationBootloader());

        // TODO : on devrait pouvoir déplacer ces bootloader dans le fichier core.php !!!!
        //$this->addBootloader(new CommandBootloader());
        //$this->addBootloader(new PublishableCollectionBootloader());
        //$this->addBootloader(new PackageManifestBootloader());
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
            $bootloader->bootload($this->container);
        } else {
            $this->bootloaders[] = $bootloader;
        }
    }

    /**
     * Boot the application's service bootloaders.
     */
    public function boot(): void
    {
        if (! $this->isBooted) {
            $this->isBooted = true;

            foreach ($this->bootloaders as $bootloader) {
                $bootloader->bootload($this->container);
            }
        }
    }
}
