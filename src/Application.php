<?php

declare(strict_types=1);

namespace Chiron;

use Chiron\Core\Container\Bootloader\BootloaderInterface;
use Chiron\Core\Container\Provider\ServiceProviderInterface;
use Chiron\Bootloader\ConfigureBootloader;
use Chiron\Bootloader\DirectoriesBootloader;
use Chiron\Bootloader\EnvironmentBootloader;
use Chiron\Bootloader\SettingsBootloader;
use Chiron\Container\Container;
use Chiron\Core\Dispatcher\DispatcherInterface;
use Chiron\Debug\ErrorHandler;
use Chiron\Exception\ApplicationException;
use Chiron\Core\Container\ServiceManager;

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

// TODO : passer la classe en final ? ou permettre de faire un extend de cette classe ???
class Application
{
    /**
     * Indicates if the botloaders stack has been "booted".
     *
     * @var bool
     */
    // TODO : renommer cette variable en $booted
    //private $isBooted = false;

    /** @var Container */
    private $container;

    /** @var BootloaderInterface[] */
    //private $bootloaders = [];

    /** @var DispatcherInterface[] */
    private $dispatchers = [];

    /** @var ServiceManager */
    private $serviceManager;

    /**
     * Private constructor. Use the method 'create()' or 'init()' to construct the application.
     *
     * @param Container $container
     */
    private function __construct(Container $container)
    {
        $this->container = $container;
        $this->serviceManager = new ServiceManager($this->container);
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
    // TODO : permettre de passer une string pour le dispatcher ca sera plus simple pour l'utilisateur. Idem pour l'ajout des providers et des bootloaders !!!!
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
    // TODO : permettre de passer une string pour le dispatcher ca sera plus simple pour l'utilisateur. Idem pour l'ajout des providers et des bootloaders !!!!
    public function addProvider(ServiceProviderInterface $provider): void
    {
        $this->serviceManager->addProvider($provider);

        //$provider->register($this->container);
    }

    // TODO : permettre à l'utilisateur de passe un tableau de string ou de BootloaderInterface. et appeller cette nouvelle méthode addBootloaders()
    // TODO : permettre de passer une string pour le dispatcher ca sera plus simple pour l'utilisateur. Idem pour l'ajout des providers et des bootloaders !!!!
    public function addBootloader(BootloaderInterface $bootloader): void
    {
        $this->serviceManager->addBootloader($bootloader);


        // if you add a bootloader after the application run(), we execute the bootloader, else we add it to the stack for an execution later.
        /*
        if ($this->isBooted) {
            $bootloader->bootload($this->container);
        } else {
            $this->bootloaders[] = $bootloader;
        }*/
    }

    /**
     * Start application and process user requests using selected dispatcher or throw an exception.
     *
     * @throws RuntimeException
     *
     * @return mixed Could be an 'int' for command-line dispatcher or 'void' for web dispatcher.
     */
    // TODO : il faudrait pas faire une vérification sur un booléen type isRunning pour éviter d'appeller plusieurs fois cette méthode (notamment depuis un Bootloader qui récupére l'application et qui essayerai d'appeller cette méthode run() !!!!)
    // TODO : renommer la méthode en "start()"  ?????
    // TODO : il faudrait s'inspirer de la méthode safelyBootAndGetHandler() qui fait un try/catch autour du boot avant de retourner la méthode qui sera executée : https://github.com/flarum/core/blob/master/src/Http/Server.php#L53
    public function run()
    {
        // TODO : il faudrait surement mettre un try/catch autour de la méthode boot() et dans le catch utiliser la classe ErrorHandler::handleException($e) pour afficher les erreurs, ca permettrait d'aoir une gestion des erreurs même si l'utilisateur n'a pas utilisé la méthode init() avec le paramétre $handleErrors à true !!!  https://github.com/spiral/framework/blob/e63b9218501ce882e661acac284b7167b79da30a/src/Boot/src/AbstractKernel.php#L146
        //$this->boot();
        $this->serviceManager->boot();

        // TODO : mettre ce code dans une méthode private "dispatch()" ????
        foreach ($this->dispatchers as $dispatcher) {
            if ($dispatcher->canDispatch()) {
                return $dispatcher->dispatch();
            }
        }

        // TODO : lever aussi une ApplicationException en début de méthode (juste aprés l'appel à la méthode ->boot()) dans le cas le tableau de $this->dispatchers est vide, car cela signifie que l'application est mal initialisée, et donc afficher un message pour demander à l'utilisateur de définir à minima un dispatcher !!!!
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
                //$bootloader->bootload();
                $bootloader->bootload($this->container);
            }
        }
    }

    // TODO : ajouter un paramétre boolen $debug pour savoir si on active ou non le error handler->register()
    // TODO : tester le cas ou on appel plusieurs fois cette méthode. Il faudra surement éviter de réinsérer plusieurs fois les bootloaders et autres service provider
    // TODO : passer en paramétre un tableau de "environment" values qui permettra d'initialiser le bootloader DotEnvBootloader::class
    // TODO : permettre de passer en paramétre une liste de "providers" ??? ca permettrait de facilement initialiser l'application avec une redéfinition de certains service par l'utilisateur !!!
    // TODO : il faudrait automatiquement ajouter le ConsoleDispatcher à l'application car on aura toujours le package chiron/console de présent pour ce framework et on doit pourvoir utiliser la console, donc ajouter d'office cette classe au tableau des dispatchers ca nous fera gagner du temps (et donc la classe Console\Bootloader\ConsoleDispatcherBootloader n'est plus nécessaire !!!!)
    public static function init(array $paths, array $values = [], bool $handleErrors = true): self
    {
        // TODO : attention il faudrait pouvoir faire un register une seule fois pour les error handlers !!!!
        // used to handle errors in the bootloaders processing.
        if ($handleErrors) {
            ErrorHandler::enable();
        }

        // TODO : il faudrait gérer le cas ou l'application est déjà "create" et qu'on récupére cette instance plutot que de rappeller la méthode. (c'est dans le cas ou l'utilisateur fait un App::create qu'il ajoute des providers ou autre et qu'ensuite il fasse un App::init pour finaliser l'initialisation !!!) Je suppose qu'il faudra garder un constante public de classe static avec l'instance (comme pour Container::$instance). Cela permettra aussi de créer une fonction globale "app()" qui retournera l'instance de la classe Application::class. Cela permettra en plus de la facade Facade\Application de passer par cette méthode pour injecter des bootloader par exemple.
        $app = self::create();

        $app->addBootloader(new DirectoriesBootloader($paths));
        $app->addBootloader(new EnvironmentBootloader($values));

        //die(var_dump(env('APP_KEY')));
        //die(var_dump(container(\Chiron\Core\Environment::class)));


        // TODO : il faudrait surement ajouter ici un bootloader pour gérer la copie des fichiers (c'est à dire le publisher) uniquement ceux qui sont définis dans le composer.json. Et ensuite avoir le bootloader ConfigureBootloader juste aprés pour charger les fichiers potentiellement copiés.
        //$app->addBootloader(new PublisherBootloader()); ????? Attention ce bootloader ne devra pas utiliser de fichiers de configuration car ils ne seront pas encore initialisés !!! (puisque la mutation est dans le bootloader suivant [ConfigureBootloader]). !!!! <=== attention ce TODO est faux, car le publisher sera executé depuis la command de la console (cad via le ConsoleDispatcher), donc on devra traverser tous les bootloaders avant de faire la copie des fichiers, donc cela ne fonctionnera pas !!!!!

        // add the config mutation + load the user configuration files from the '@config' folder.
        $app->addBootloader(new ConfigureBootloader($app->container)); // TODO : ce bootloader devrait pas être dans le package chiron/core ????
        // init the application settings (debug, charset, timezone...etc).
        // TODO : permettre de faire un override des valeurs par défault des settings en lui passant un tableau dans le constructeur de la classe SettingsBootloader + ajouter un paramétre à la méthode application::init() cela permettrait lors des tests phpunit de facilement modifier ces valeurs !!!!
        $app->addBootloader(new SettingsBootloader());

        // TODO : code provisoire !!!!
        Container::$instance->singleton(\Chiron\Console\Console::class);
        Container::$instance->singleton(\Symfony\Component\Console\CommandLoader\CommandLoaderInterface::class, \Chiron\Core\Command\CommandLoader::class);

        $app->addBootloader(resolve(\Chiron\Bootloader\ConsoleDispatcherBootloader::class));
        $app->addBootloader(resolve(\Chiron\Bootloader\PublishConsoleBootloader::class));
        $app->addBootloader(resolve(\Chiron\Bootloader\ConsoleBootloader::class));

        $app->addBootloader(resolve(\Chiron\Core\Bootloader\PublishSettingsBootloader::class));

        self::configure($app);

        return $app;
    }

    // TODO : il faudrait surement récupérer l'instance déjà créée précédemment pour limiter la création de nouvelles instance à chaque appel de cette méthode. Eventuellement passer un booleen "$forceNew = false" en paramétre pour soit créer une nouvelle instance soit recuperer l'ancienne instance (via une propriété de classe public et statique $instance). Attention si on récupére l'instance il faudra faire un reset sur la valeur du boolen $this->isBooted car si l'utilisateur a fait un appel dans cette ordre : create/boot/init on va avoir un probléme lorsqu'on va vouloir faire le run()
    // TODO : à renommer en getInstance() ????
    // TODO : méthode à faire passer en privée ???
    public static function create(): self
    {
        $container = new Container();

        $app = new self($container);
        $container->singleton(self::class, $app); // TODO : déplacer ce bind singleton directement dans le constructeur ???

        return $app;
    }

    // TODO : il y a surement des services à ne pas charger si on est en mode console !!! et inversement il y en a surement à charger uniquement en mode console !!!
    private static function configure(Application $app): void
    {
        // TODO : forcer le chargement manuellement (cad en ajoutant le ConsoleDispatcherBootloader) du console Dispatcher car si il y a un probléme dans le fichier .../runtime/cache/packages.json (par exemple il existe mais il est vide ou obsoléte pour la version de la console) le PackageManifestBootLoader ne va pas recréer ce fichier de cache et donc il ne chargera pas le console dispatcher et on aura une erreur, par exemple si on essaye quand même de vider le cache via la commande "cache:clear" on aura une erreur pour indiquer qu'aucun dispatcher actif n'a été trouvé...

        //**************************
        //******** PROVIDER ********
        //**************************
        //$app->addProvider(new \Chiron\Provider\HttpFactoriesServiceProvider());
        //$app->addProvider(new \Chiron\Http\ErrorHandler\Provider\HttpErrorHandlerServiceProvider());

        //**************************
        //******* BOOTLOADER *******
        //**************************
        // TODO : attention si il y a des bootloaders chargés via le packagemanifest qui ajoutent une commande dans la console, si cette commande utilise le même nom que les commandes par défaut  définies dans la classe CommandBootloader, elles vont être écrasées !!!! faut il faire un test dans cette classe si la command est déjà définie dans la console on ne l'ajoute pas ????? ou alors écrase la commande d'office ???? ou alors lever une exception car on aura un doublon sur le nom de la commande ce qui n'est pas logique ????
        $app->addBootloader(new \Chiron\Bootloader\CommandBootloader());
        $app->addBootloader(new \Chiron\Bootloader\PublishAppBootloader());
        $app->addBootloader(new \Chiron\Bootloader\PackageManifestBootloader());
        $app->addBootloader(new \Chiron\Bootloader\ApplicationBootloader());

        //$app->addBootloader(new \Chiron\Bootloader\TestBootloader());
    }
}
