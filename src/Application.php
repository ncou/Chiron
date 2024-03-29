<?php

declare(strict_types=1);

namespace Chiron;

use Chiron\Core\Container\Bootloader\BootloaderInterface;
use Chiron\Core\Container\Provider\ServiceProviderInterface;
use Chiron\Service\Bootloader\ConfigureBootloader;
use Chiron\Service\Bootloader\DirectoriesBootloader;
use Chiron\Service\Bootloader\EnvironmentBootloader;
use Chiron\Service\Bootloader\SettingsBootloader;
use Chiron\Service\Bootloader\EventsBootloader;
use Chiron\Container\Container;
use Chiron\Core\Engine\EngineInterface;
use Chiron\Debug\ErrorHandler;
use Chiron\Core\Exception\ImproperlyConfiguredException;
use Chiron\Service\ServiceManager;
use Chiron\Core\Container\ContainerFactory;
use Chiron\Service\Provider\CoreServiceProvider;
use Chiron\Config\InjectableConfigInterface;
use Chiron\Core\Container\Mutation\InjectableConfigMutation;
use Chiron\Service\Bootloader\PackageManifestBootloader;
use Chiron\Service\Bootloader\ServicesBootloader;
use Chiron\Engine\ConsoleEngine;

// TODO : créer un StartEvent ou StartupEvent, ainsi qu'un TerminateEvent ou ShutdownEvent ou ExitEvent ou StopEvent/EndEvent. créer un InitEvent/InitializeEvent ???

//Exemple avec tous les events gérés dans Symfony :
//-------------------------------------------------
//https://symfony.com/doc/current/reference/events.html


// TODO : créer une méthode close() qui fait un exit avec le message passer en paramétre ? et eventuellement appeller un event du genre ApplicationCloseEvent::class
//https://github.com/ventoviro/windwalker-application/blob/master/AbstractApplication.php#L64
//https://github.com/yiisoft/yii-web/blob/master/src/Application.php#L46

// TODO : Exemple d'événements :
//https://github.com/ventoviro/windwalker-core/tree/a68508411be9c85003a1081e862eda67c897fd8e/src/Core/Events
//https://github.com/yiisoft/yii-web/tree/master/src/Event

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

// TODO : ajouter une méthode pour trouver les commandes ajoutées à l'application, un "findCommand($name)".
// Exemple : https://github.com/symfony/symfony/blob/master/src/Symfony/Bundle/FrameworkBundle/Console/Application.php#L112
// Exemple : https://github.com/symfony/console/blob/master/Application.php#L595

// TODO : au lieu d'avoir la variable de classe public il faudrait plutot avoir une méthode public getContainer() dans cette classe.
class Application
{
    /** @var EngineInterface[] */
    protected $engines = [];
    /** @var ServiceManager */
    public $services; // TODO : virer la notion de public car ce n'est pas utilisé dans les packages !!!!

    // TODO : ca serait bien de passer directement un ServiceManager dans le constructeur plutot qu'on objet container qu'on ne devrait pas manipuler directement dans cette classe !!!!
    // TODO : passer le constructeur en public et lever une exception si on essaye d'instancier cette classe sans passer par la méthode static::create() !!!! éventuellement utiliser cette classe d'utilitaire pour limiter les appels statics et le constructeur : https://github.com/nette/utils/blob/0e350ef848fa9586eac0748479ca013e8fab6cbd/src/StaticClass.php
    // TODO : ajouter automatiquement le ConsoleDispatcher dans le tableau $this->dispatchers[] ??? ca éviterai de faire un appel au ConsoleDispatcherBootloader !!!

    // TODO : ajouter un paramétre boolen $debug pour savoir si on active ou non le error handler->register()
    // TODO : tester le cas ou on appel plusieurs fois cette méthode. Il faudra surement éviter de réinsérer plusieurs fois les bootloaders et autres service provider
    // TODO : passer en paramétre un tableau de "environment" values qui permettra d'initialiser le bootloader DotEnvBootloader::class
    // TODO : permettre de passer en paramétre une liste de "providers" ??? ca permettrait de facilement initialiser l'application avec une redéfinition de certains service par l'utilisateur !!!
    // TODO : il faudrait automatiquement ajouter le ConsoleDispatcher à l'application car on aura toujours le package chiron/console de présent pour ce framework et on doit pourvoir utiliser la console, donc ajouter d'office cette classe au tableau des dispatchers ca nous fera gagner du temps (et donc la classe Console\Bootloader\ConsoleDispatcherBootloader n'est plus nécessaire !!!!)
    // TODO : déplacer tout ce code directement dans le constructeur ? En passant le constructeur en public, car je ne pense pas qu'il y ait un interet particulier à garder une méthode static init !!!!
    // TODO : il faudrait surement gérer une structure de répertoires par défaut si on ne passe pas de valeur pour le paramétre $paths !!!
    public function __construct(array $paths, array $values = [])
    {
        // TODO : il faudrait gérer le cas ou l'application est déjà "create" et qu'on récupére cette instance plutot que de rappeller la méthode. (c'est dans le cas ou l'utilisateur fait un App::create qu'il ajoute des providers ou autre et qu'ensuite il fasse un App::init pour finaliser l'initialisation !!!) Je suppose qu'il faudra garder un constante public de classe static avec l'instance (comme pour Container::$instance). Cela permettra aussi de créer une fonction globale "app()" qui retournera l'instance de la classe Application::class. Cela permettra en plus de la facade Facade\Application de passer par cette méthode pour injecter des bootloader par exemple.
        $this->services = new ServiceManager(); // TODO : créer une méthode privée self::create() qui retournerait l'application crééer, on pourrait aussi mettre dans cette fonction les lignes de code qui permettent de charger le CoseServiceProvider, et la ligne qui fait le bindsingleton de l'instance $app. Eventuellement utiliser aussi cette méthode pour faire le addDispatcher(new ConsoleDispatcher()) !!!

        // +++ Bind all the core services. +++
        $this->services->addProvider(new CoreServiceProvider()); // TODO : il faudrait créer dans ce CoreServiceProvider une partie pour binder en singleton une classe ServiceManager et qui utiliserait le ServicesConfig pour initialiser cette classe !!!! cela permettra de supprimer la classe ServicesBootloader (en fait on ferait comme pour les fichiers de config events et console/settings !!!!)
        // +++ Boot all the app services. +++
        $this->services->addBootloader(new DirectoriesBootloader($paths));
        $this->services->addBootloader(new EnvironmentBootloader($values));

        // init the application settings (debug, charset, timezone...etc).
        // TODO : permettre de faire un override des valeurs par défault des settings en lui passant un tableau dans le constructeur de la classe SettingsBootloader + ajouter un paramétre à la méthode application::init() cela permettrait lors des tests phpunit de facilement modifier ces valeurs !!!!
        $this->services->addBootloader(new SettingsBootloader());

        // TODO : utiliser le $container pour créer la classe new ConsoleDispatcher($container) qui sera ajouté à l'application via la commande $app->addDispatcher($consoleDispatcher)
        //$this->services->addBootloader(new \Chiron\Bootloader\ConsoleDispatcherBootloader());

        // TODO : il y a surement des services à ne pas charger si on est en mode console !!! et inversement il y en a surement à charger uniquement en mode console !!!
        // TODO : attention si il y a des bootloaders chargés via le packagemanifest qui ajoutent une commande dans la console, si cette commande utilise le même nom que les commandes par défaut  définies dans la classe CommandBootloader, elles vont être écrasées !!!! faut il faire un test dans cette classe si la command est déjà définie dans la console on ne l'ajoute pas ????? ou alors écraser la commande d'office ???? ou alors lever une exception car on aura un doublon sur le nom de la commande ce qui n'est pas logique ????

        $this->services->addBootloader(new PackageManifestBootloader());
        $this->services->addBootloader(new ServicesBootloader());

        /** @psalm-var Container $container */
        $container = $this->services->container;

        // TODO : lever un évenement de type "InitEvent" en attachant l'application comme paramétre, et c'est à ce moment là que le listener ajouterai le ConsoleEngine !!!!
        $this->addEngine(new ConsoleEngine($container->injector()));

        // TODO : il faudrait récupérer ici le eventdispatcher et faire un trigger sur l'événement InitApplicationEvent::class avec l'instance de l'application à stocker dans ce event. On ajoutera un listener dans les packages Sapi/Roadrunner/Workerman/ReactPHP pour injecter le EngineInterface pour ces différents packages.

        // Bind the application instance as singleton in the container.
        $container->singleton(self::class, $this); // TODO : attention comme on peut faire un extends de cette classe il faut s'assurer que la clés self::class est correcte, sinon il faudrait plutot utiliser static::class => Faire des tests pour s'assurer de ce qui fonctionne !!!!

        $this->initialize($container);
    }

    /**
     * Constructor hook method.
     *
     * Implement this method to avoid having to overwrite the constructor and call parent.
     *
     * @param Container $container The container instance provided to this application.
     *
     * @return void
     */
    public function initialize(Container $container): void
    {
    }

    /**
     * Add new engine. This method must only be called before method `start` will be invoked.
     *
     * @param EngineInterface $engine
     */
    // TODO : il faudrait gérer le cas ou l'on souhaite ajouter un dispatcher au dessus de la stack. Ajouter un paramétre 'bool $onTop = false' à cette méthode ????
    // TODO : permettre de gérer les dispatchers dans les fichiers composer.json (partie "extra") et les charger via le packagemanifest ????
    // TODO : permettre de passer une string en paramétre et utiliser le container qui est aussi un FactoryInterface pour "créer" la classe passée en paramétre !!!
    // TODO : permettre de passer une string pour le engine ca sera plus simple pour l'utilisateur. utiliser la fonction "resolve()" et vérifier que le type de retour est bien un objet qui implémente EngineInterface !!!!
    public function addEngine(EngineInterface $engine): void
    {
        $this->engines[] = $engine;
    }

    /**
     * Start application and process user requests using selected dispatcher or throw an exception.
     *
     * @throws ImproperlyConfiguredException
     *
     * @return mixed Could be an 'int' for command-line dispatcher or 'void' for web dispatcher.
     */
    // TODO : il faudrait pas faire une vérification sur un booléen type isRunning pour éviter d'appeller plusieurs fois cette méthode (notamment depuis un Bootloader qui récupére l'application et qui essayerai d'appeller cette méthode run() !!!!)
    // TODO : il faudrait s'inspirer de la méthode safelyBootAndGetHandler() qui fait un try/catch autour du boot avant de retourner la méthode qui sera executée : https://github.com/flarum/core/blob/master/src/Http/Server.php#L53
    public function start()
    {
        // TODO : Eventuellement lever un événement de type ApplicationStartupEvent::class avec en paramétre le microtime pour connaitre l'heure de début de l'application.
        // TODO : il faudrait surement mettre un try/catch autour de la méthode boot() et dans le catch utiliser la classe ErrorHandler::handleException($e) pour afficher les erreurs, ca permettrait d'aoir une gestion des erreurs même si l'utilisateur n'a pas utilisé la méthode init() avec le paramétre $handleErrors à true !!!  https://github.com/spiral/framework/blob/e63b9218501ce882e661acac284b7167b79da30a/src/Boot/src/AbstractKernel.php#L146
        $this->services->boot();

        // TODO : récupérer le EventDispatcher dans le service->container et trigger l'evenement ApplicationStartedEvent::class avec en paramétre le $this pour qu'on puisse manipuler l'application (par exemple pour ajouter un engine !!!!). ou l'event ApplicationBootedEvent::class



        // Dispatch the request based on the environment values (ex: Console or Web dispatcher).
        foreach ($this->engines as $engine) {
            if ($engine->isActive()) {
                // TODO : Lever ici un evenement de type EngineFoundEvent::class ou EngineMatchedEvent::class avec en paramétre le $engine pour avoir l'instance (et donc le getclassname) dans l'événement.
                // TODO : Eventuellement lever un événement de type ApplicationShutdownEvent::class avec en paramétre le microtime pour connaitre l'heure de fin de l'application avant de retourner la réponse. https://github.com/yiisoft/yii-web/blob/master/src/Application.php#L46
                return $engine->ignite();
            }
        }

        // TODO : lever aussi une ApplicationException en début de méthode (juste aprés l'appel à la méthode ->boot()) dans le cas le tableau de $this->dispatchers est vide, car cela signifie que l'application est mal initialisée, et donc afficher un message pour demander à l'utilisateur de définir à minima un dispatcher !!!!
        // TODO : utiliser plutot l'exception ImproperlyConfiguredException ???? qui est dans le package chiron/core, cela permettra de virer la classe ApplicationException !!!!
        // TODO : créer une exception EngineNotFoundException ???? eventuellement la faire étendre de l'exception ImproperlyConfiguredException ????
        throw new ImproperlyConfiguredException('Unable to locate active engine for application.'); // TODO : afficher le contenu du tableau $this->engines ? genre on affiche un message différent si il est vide, et sinon on affiche une liste des classname en utilisant un array_map + un explode($engines, ',') ????
    }

    /**
     * is utilized for reading data from inaccessible members.
     *
     * @param   $name  string
     *
     * @return  mixed
     */
    //https://github.com/ventoviro/windwalker-application/blob/master/AbstractApplication.php#L222
    /*
    public function __get($name)
    {
        $allowNames = [
            'config',
        ];

        if (in_array($name, $allowNames)) {
            return $this->$name;
        }

        throw new \UnexpectedValueException('Property: ' . $name . ' not found in ' . get_called_class());
    }*/

    /**
     * is utilized for reading data from inaccessible members.
     *
     * @param   $name  string
     *
     * @return  mixed
     */
    //https://github.com/ventoviro/windwalker-application/blob/master/AbstractWebApplication.php#L400
    /*
    public function __get($name)
    {
        $allowNames = [
            'environment',
            'server',
        ];

        if (in_array($name, $allowNames, true)) {
            return $this->$name;
        }

        $getters = [
            'uri',
            'request',
            'browser',
            'platform',
        ];

        if (in_array(strtolower($name), $getters, true)) {
            $method = 'get' . ucfirst($name);

            return $this->$method();
        }

        return parent::__get($name);
    }*/


    /**
     * Method to close the application.
     *
     * @param   integer|string $message The exit code (optional; default is 0).
     *
     * @return  void
     *
     * @since   2.0
     */
    /*
    public function close($message = 0)
    {
        exit($message);
    }*/
}
