<?php

declare(strict_types=1);

namespace Chiron;

use Chiron\Boot\Directories;
use Chiron\Boot\Environment;
use Chiron\Bootload\BootloaderInterface;
use Chiron\Bootload\Configurator;
use Chiron\Bootload\ServiceProvider\ServiceProviderInterface;
use Chiron\Container\SingletonInterface;
use Chiron\Dispatcher\DispatcherInterface;
use Chiron\ErrorHandler\RegisterErrorHandler;
use Chiron\Exception\ApplicationException;

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
class Application // implements SingletonInterface
{
    /** @var DispatcherInterface[] */
    private $dispatchers = [];

    /** @var Configurator */
    private $configurator;

    /**
     * @param Configurator $configurator
     */
    public function __construct(Configurator $configurator)
    {
        $this->configurator = $configurator;
    }

    // TODO : réfléchir si on conserve cette fonction. Eventuellement si l'utilisateur souhaite booter manuellement le configurator. Mais à voir ce que cela apporte !!!!
    public function getConfigurator(): Configurator
    {
        return $this->configurator;
    }

    // TODO : réfléchir si on conserve cette méthode, car elle n'est pas vraiment utile. L'utilisateur peut utiliser l'instance partagée du container ou la fonction "container()", donc utilité limité de cette fonction getContainer.
    public function getContainer(): Container
    {
        return $this->configurator->getContainer();
    }

    /**
     * Add new dispatcher. This method must only be called before method `start` will be invoked.
     *
     * @param DispatcherInterface $dispatcher
     */
    // TODO : il faudrait gérer le cas ou l'on souhaite ajouter un dispatcher au dessus de la stack. Ajouter un paramétre 'bool $onTop = false' à cette méthode ????
    public function addDispatcher(DispatcherInterface $dispatcher): void
    {
        $this->dispatchers[] = $dispatcher;
    }

    /**
     * Start application and process user requests using selected dispatcher or throw an exception.
     *
     * @throws RuntimeException
     *
     * @return mixed Could be an 'int' for command-line dispatcher or 'void' for web dispatcher.
     */
    public function start()
    {
        $this->configurator->boot();

        foreach ($this->dispatchers as $dispatcher) {
            if ($dispatcher->canDispatch()) {
                return $dispatcher->dispatch();
            }
        }

        throw new ApplicationException('Unable to locate active dispatcher.');
    }

    public function addProvider(ServiceProviderInterface $provider): void
    {
        $this->configurator->addProvider($provider);
    }

    public function addBootloader(BootloaderInterface $bootloader): void
    {
        $this->configurator->addBootloader($bootloader);
    }

    // TODO : on devrait accepter uniquement des objets CommandInterface et non pas string !!! utiliser un CommandLoader pour résoudre les commandes sous forme de chaine qui sont des nom de classe à résoudre depuis le container !!!
    public function addCommand(string $command): void
    {
        $this->configurator->addCommand($command);
    }

    public function addMutation($todo): void
    {
        $this->configurator->addMutation($todo);
    }

    // TODO : utiliser le $basePath dans le code du mapDirectories() (il faudrait que le paramétre soit une tableau) !!!!
    // TODO : ajouter un rtrim($basepath, '\/') sur le basePath ?????
    // TODO : ajouter un paramétre boolen $debug pour savoir si on active ou non le error handler->register()
    //public static function init(string $basePath = null): self
    public static function init(array $directories): self
    {
        RegisterErrorHandler::register();

        $configurator = new Configurator();

        // TODO : à déporter ce bout de code pour initialiser le Directories et le Environement classe dans un serviceprovider cad dans un fichier séparé !!!!
        // TODO : passer un array directory en paramétre de la fonction init() plutot qu'un basePath, cela évitera d'utiliser un tableau vide.
        //$directories = [];
        $configurator->getContainer()->share(Directories::class, new Directories(static::mapDirectories($directories)));

        // TODO : déplacer ce bout de code dans la classe SharedServiceProvider. Ca permet juste d'économiser un peu de mémoire en évitant de refaire une instanciation à chaque fois.
        $configurator->getContainer()->share(Environment::class);

        //return new static($configurator);
        // TODO : attention faire un test si cela fonctionne correctement dans le cas ou l'utilisateur créé une classe "App" par exemple qui extends de Application::class
        // TODO : il faudrait plutot faire un $instance = new static($configurator); et immédiatement stocker cette instance de Application dans le container en utilisant la clés seft::class et static::class pour binder l'instance en singleton !!!!
        return $configurator->getContainer()->get(static::class);
    }

    /**
     * Normalizes directory list and adds all required aliases.
     *
     * @param array $directories
     *
     * @return array
     */
    // TODO : attention faire un test si cela fonctionne correctement dans le cas ou l'utilisateur créé une classe "App" par exemple qui extends de Application::class
    protected static function mapDirectories(array $directories): array
    {
        if (! isset($directories['root'])) {
            //throw new ApplicationException("Missing required directory 'root'.");

            if (PHP_SAPI === 'cli') {
                $rootPath = getcwd();
            } else {
                $rootPath = realpath(getcwd() . '/../');
            }

            $directories['root'] = $rootPath;
        }

        if (! isset($directories['app'])) {
            $directories['app'] = $directories['root'] . '/app';
        }

        // TODO : utiliser un DIRECTORY_SEPARATOR au lieu de mettre un "/" en dur dans les chemins
        // TODO : utiliser le répertoire 'var' à la place de runtime + modifier la console command RuntimeCommand qui permet de vérifier les droits en lecture/écriture du répertoire
        // TODO : il faudrait pas ajouter un répertoire pour les logs ???? => https://github.com/spiral/app/blob/85705bb7a0dafd010a83fa4bcc7323b019d8dda3/app/src/Bootloader/LoggingBootloader.php#L29
        // TODO : déplacer le répertoire template dans le répertoire ressources ???? éventuellement le renommer en views au lieu de templates !!!!
        $directories = array_merge([
            // public root
            'public'    => $directories['root'] . '/public',
            // vendor libraries
            'vendor'    => $directories['root'] . '/vendor',
            // templates libraries
            'templates'    => $directories['root'] . '/templates',
            // data directories
            'runtime'   => $directories['root'] . '/runtime',
            'cache'     => $directories['root'] . '/runtime/cache',
            // application directories
            //'config'    => $directories['app'] . '/config/',
            'config'    => $directories['root'] . '/config',
            'resources' => $directories['app'] . '/resources',
        ], $directories);

        if (! is_writable($directories['runtime'])) {
            // TODO : faire un normalizePath sur le répertoire car quand on l'affiche on a des slash et antislash et la présence éventuelle de '/../' dans le chemin.
            throw new ApplicationException('The ' . $directories['runtime'] . ' directory must be present and writable.');
        }

        return $directories;
    }

    /*
    //https://github.com/laravel/framework/blob/7.x/src/Illuminate/Foundation/Application.php#L303
        protected function bindPathsInContainer()
        {
            $this->instance('path', $this->path());
            $this->instance('path.base', $this->basePath());
            $this->instance('path.lang', $this->langPath());
            $this->instance('path.config', $this->configPath());
            $this->instance('path.public', $this->publicPath());
            $this->instance('path.storage', $this->storagePath());
            $this->instance('path.database', $this->databasePath());
            $this->instance('path.resources', $this->resourcePath());
            $this->instance('path.bootstrap', $this->bootstrapPath());
        }
        */
}
