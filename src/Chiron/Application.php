<?php

declare(strict_types=1);

namespace Chiron;

use Chiron\Bootload\BootloaderInterface;
use Chiron\Bootload\ServiceProvider\ServiceProviderInterface;
use Chiron\Bootloader\ConfigureBootloader;
use Chiron\Bootloader\DirectoriesBootloader;
use Chiron\Bootloader\EnvironmentBootloader;
use Chiron\Bootloader\SettingsBootloader;
use Chiron\Container\Container;
use Chiron\Dispatcher\DispatcherInterface;
use Chiron\ErrorHandler\RegisterErrorHandler;
use Chiron\Exception\ApplicationException;

//https://github.com/swoft-cloud/swoft-framework/blob/0702d93baf8ee92bc4d1651fe0cda2a022197e98/src/SwoftApplication.php

//https://github.com/symfony/symfony/blob/master/src/Symfony/Component/HttpKernel/Kernel.php
//https://github.com/drupal/core/blob/4576cfa33ea2d49e6b956795d474ee89972b1d59/lib/Drupal/Component/DependencyInjection/Container.php

// RESET SERVICE : http://apigen.juzna.cz/doc/redaxo/redaxo/class-Symfony.Contracts.Service.ResetInterface.html

/**
 * This constant defines the framework installation directory.
 */
//defined('CHIRON_PATH') or define('CHIRON_PATH', __DIR__);

/**
 * The application framework core.
 */
// TODO : il faudrait pas une méthode setDispatchers($array) / getDispatchers(): array ????
// TODO : ajouter des méthodes set/getName() pour le nom de l'application idem pour get/setVersion()
// TODO : créer une classe d'exception dédiée à l'pplication ???? ApplicationException qui étendrait de RuntimeException. Elle serai levée si le rootPath est manquant ou si aucun dispatcher n'est trouvé.
// TODO : passer la classe en "final" ??? ou alors permettre de faire un extends de cette classe ???? bien réfléchir !!!!

// TODO : ajouter une méthode pour trouver les commandes ajoutées à l'application, un "findCommand($name)".
// Exemple : https://github.com/symfony/symfony/blob/master/src/Symfony/Bundle/FrameworkBundle/Console/Application.php#L112
// Exemple : https://github.com/symfony/console/blob/master/Application.php#L595

class Application
{
    /**
     * Indicates if the botloaders stack has been "booted".
     *
     * @var bool
     */
    // TODO : renommer cette variable en $booted
    private $isBooted = false;

    /** @var Container */
    private $container;

    /** @var BootloaderInterface[] */
    private $bootloaders = [];

    /** @var DispatcherInterface[] */
    private $dispatchers = [];

    /**
     * Private constructor. Use the method 'create()' or 'init()' to construct the application.
     *
     * @param Container $container
     */
    private function __construct(Container $container)
    {
        $this->container = $container;
    }

    // TODO : il faudrait pas initialiser le container avant de le retourner ??? ou alors cela risque de poser problémes ?????
    // TODO : méthode pas vraiment utile à la limite retourner un ContainerInterface plutot qu'un container, et donc éviter que l'utilisateur ne puisse accéder aux fonction bind/singleton/inflect...etc de l'object Container. L'utilisateur aura seulement accés aux méthodes get/has de base.
    // TODO : renommer cette méthode en "container()" ? ca serai plus simple pour chainer les instructions : $app->container()->make(xxxx);
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * Add new dispatcher. This method must only be called before method `start` will be invoked.
     *
     * @param DispatcherInterface $dispatcher
     */
    // TODO : il faudrait gérer le cas ou l'on souhaite ajouter un dispatcher au dessus de la stack. Ajouter un paramétre 'bool $onTop = false' à cette méthode ????
    // TODO : permettre de gérer les dispatchers dans les fichiers composer.json (partie "extra") et les charger via le packagemanifest ????
    // TODO : permettre de passer une string en paramétre et utiliser le container qui est aussi un FactoryInterface pour "créer" la classe passée en paramétre !!!
    public function addDispatcher(DispatcherInterface $dispatcher): void
    {
        $this->dispatchers[] = $dispatcher;
    }

    /**
     * Register a service provider with the application.
     *
     * @param ServiceProviderInterface $provider
     */
    // TODO : améliorer le code : https://github.com/laravel/framework/blob/5.8/src/Illuminate/Foundation/Application.php#L594
    //public function register($provider)
    // TODO : permettre à l'utilisateur de passe un tableau de string ou de ServiceProviderInterface. et appeller cette nouvelle méthode addProviders()
    public function addProvider(ServiceProviderInterface $provider): void
    {
        $provider->register($this->container);
    }

    // TODO : permettre à l'utilisateur de passe un tableau de string ou de BootloaderInterface. et appeller cette nouvelle méthode addBootloaders()
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
     * Start application and process user requests using selected dispatcher or throw an exception.
     *
     * @throws RuntimeException
     *
     * @return mixed Could be an 'int' for command-line dispatcher or 'void' for web dispatcher.
     */
    // TODO : il faudrait pas faire une vérification sur un booléen type isRunning pour éviter d'appeller plusieurs fois cette méthode (notamment depuis un Bootloader qui récupére l'application et qui essayerai d'appeller cette méthode run() !!!!)
    public function run()
    {
        $this->boot();

        // TODO : mettre ce code dans une méthode private "dispatch()" ????
        foreach ($this->dispatchers as $dispatcher) {
            if ($dispatcher->canDispatch()) {
                return $dispatcher->dispatch();
            }
        }

        // TODO : configurer le message dans le cas ou le tableau de dispatcher est vide c'est que l'application n'a pas été correctement initialisée ????
        // TODO : créer une exception DispatcherNotFoundException qui héritera de ApplicationException.
        throw new ApplicationException('Unable to locate active dispatcher.');
    }

    /**
     * Boot the application's service bootloaders.
     */
    // TODO : gérer le cas ou l'utilisateur appel dans cette ordre les méthodes => create() / boot() / init() dans ce cas dans la méthode init() il faudra lever une ApplicationException si le booléen $isBooted est à true. Réfléchir aussi au cas ou  l'utilisateur fait un init()/boot()/create c'est la même problématique.
    // TODO : pourquoi ne pas mettre cette méthode en private ????
    public function boot(): void
    {
        if (! $this->isBooted) {
            $this->isBooted = true;

            foreach ($this->bootloaders as $bootloader) {
                $bootloader->bootload($this->container);
            }
        }
    }

    // TODO : il faudrait surement récupérer l'instance déjà créée précédemment pour limiter la création de nouvelles instance à chaque appel de cette méthode. Eventuellement passer un booleen "$forceNew = false" en paramétre pour soit créer une nouvelle instance soit recuperer l'ancienne instance (via une propriété de classe public et statique $instance). Attention si on récupére l'instance il faudra faire un reset sur la valeur du boolen $this->isBooted car si l'utilisateur a fait un appel dans cette ordre : create/boot/init on va avoir un probléme lorsqu'on va vouloir faire le run()
    // TODO : à renommer en getInstance() ????
    public static function create(): self
    {
        $container = new Container();
        $container->setAsGlobal();

        $app = new self($container);
        $container->singleton(self::class, $app);

        return $app;
    }

    // TODO : ajouter un paramétre boolen $debug pour savoir si on active ou non le error handler->register()
    // TODO : tester le cas ou on appel plusieurs fois cette méthode. Il faudra surement éviter de réinsérer plusieurs fois les bootloaders et autres service provider
    // TODO : passer en paramétre un tableau de "environment" values qui permettra d'initialiser le bootloader DotEnvBootloader::class
    // TODO : permettre de passer en paramétre une liste de "providers" ??? ca permettrait de facilement initialiser l'application avec une redéfinition de certains service par l'utilisateur !!!
    public static function init(array $paths, array $values = [], bool $handleErrors = true): self
    {
        // TODO : attention il faudrait pouvoir faire un register une seule fois pour les error handlers !!!!
        // used to handle errors in the bootloaders processing.
        if ($handleErrors) {
            RegisterErrorHandler::enable();
        }

        // TODO : il faudrait gérer le cas ou l'application est déjà "create" et qu'on récupére cette instance plutot que de rappeller la méthode. (c'est dans le cas ou l'utilisateur fait un App::create qu'il ajoute des providers ou autre et qu'ensuite il fasse un App::init pour finaliser l'initialisation !!!) Je suppose qu'il faudra garder un constante public de classe static avec l'instance (comme pour Container::$instance). Cela permettra aussi de créer une fonction globale "app()" qui retournera l'instance de la classe Application::class. Cela permettra en plus de la facade Facade\Application de passer par cette méthode pour injecter des bootloader par exemple.
        $app = self::create();

        $app->addBootloader(new DirectoriesBootloader($paths));
        $app->addBootloader(new EnvironmentBootloader($values));

        // TODO : il faudrait surement ajouter ici un bootloader pour gérer la copie des fichiers (c'est à dire le publisher) uniquement ceux qui sont définis dans le composer.json. Et ensuite avoir le bootloader ConfigureBootloader juste aprés pour charger les fichiers potentiellement copiés.
        //$app->addBootloader(new PublisherBootloader()); ????? Attention ce bootloader ne devra pas utiliser de fichiers de configuration car ils ne seront pas encore initialisés !!! (puisque la mutation est dans le bootloader suivant [ConfigureBootloader]). !!!! <=== attention ce TODO est faux, car le publisher sera executé depuis la command de la console (cad via le ConsoleDispatcher), donc on devra traverser tous les bootloaders avant de faire la copie des fichiers, donc cela ne fonctionnera pas !!!!!

        // add the config mutation + load the user configuration files from the '@config' folder.
        $app->addBootloader(new ConfigureBootloader($app->container));
        // init the application settings (debug, charset, timezone...etc).
        // TODO : permettre de faire un override des valeurs par défault des settings en lui passant un tableau dans le constructeur de la classe SettingsBootloader + uajouter un paramétre à la méthode application::init() cela permettrait lors des tests phpunit de facilement modifier ces valeurs !!!!
        $app->addBootloader(new SettingsBootloader());

        self::configure($app);

        return $app;
    }

    // TODO : il y a surement des services à ne pas charger si on est en mode console !!! et inversement il y en a surement à charger uniquement en mode console !!!
    private static function configure(Application $app): void
    {
        // NullLogger Service + LoggerAwareInterface mutation !!!!
        $app->addProvider(new \Chiron\Provider\LoggerServiceProvider());
        $app->container->inflector(\Psr\Log\LoggerAwareInterface::class, [\Chiron\Logger\LoggerAwareMutation::class, 'mutation']);

        self::coreSettings_A_VIRER($app);
    }

    private static function coreSettings_A_VIRER(Application $app): void
    {
        // TODO : normalement on devrait avoir un tableau vide et les providers ci dessous seraient chargés soit par le PackageManifest qui scan les packages, soit via le app.php pour le dernier provider (database)

        //************************
        //******  PROVIDER *******
        //************************
        $app->addProvider(new \Chiron\Provider\ServerRequestCreatorServiceProvider());
        $app->addProvider(new \Chiron\Provider\HttpFactoriesServiceProvider());
        $app->addProvider(new \Chiron\Provider\ErrorHandlerServiceProvider());
        //$app->addProvider(new \Chiron\Provider\RoadRunnerServiceProvider());

        //**************************
        //******  BOOTLOADER *******
        //**************************
        $app->addBootloader(new \Chiron\Bootloader\PublishableCollectionBootloader());
        $app->addBootloader(new \Chiron\Bootloader\PackageManifestBootloader());

        // TODO : attention si il y a des bootloaders chargés via le packagemanifest qui ajoutent une commande dans la console, si cette commande utilise le même nom que les commandes par défaut  définies dans la classe CommandBootloader, elles vont être écrasées !!!! faut il faire un test dans cette classe si la command est déjà définie dans la console on ne l'ajoute pas ????? ou alors écrase la commande d'office ????
        $app->addBootloader(new \Chiron\Bootloader\CommandBootloader());

        // TODO : déplacer ces bootloader dans les packages templates/router/http et ajouter dans le composer.json une balise extra avec les informations pour charger ces classes.
        //Chiron\Bootloader\ViewsBootloader::class,
        $app->addBootloader(new \Chiron\Bootloader\HttpBootloader());
        //Chiron\Bootloader\RouteCollectorBootloader::class,

        $app->addBootloader(new \Chiron\Bootloader\ConsoleBootloader());
        $app->addBootloader(new \Chiron\Bootloader\ApplicationBootloader());

        //$app->addBootloader(new \Chiron\Bootloader\TestBootloader());
    }
}
