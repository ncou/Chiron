<?php

declare(strict_types=1);

namespace Chiron\Service\Provider;

use Chiron\Container\Container;
use Chiron\Core\Container\Bootloader\BootloaderInterface;
use Chiron\Core\Container\Provider\ServiceProviderInterface;
use Chiron\Container\BindingInterface;
use Chiron\Container\ContainerAwareInterface;

use Chiron\Core\Directories;

use Chiron\Config\InjectableConfigInterface;
use Chiron\Service\Mutation\InjectableConfigMutation;
use Chiron\Service\Mutation\ContainerAwareMutation;

use Chiron\Publisher\Publisher;
use Chiron\Config\Configure;
use Chiron\Console\Console;
use Chiron\Core\Command\CommandLoader;
use Chiron\Config\ConsoleConfig;

use Chiron\Config\Loader\LoaderInterface;
use Chiron\Config\Loader\PhpLoader;
use Closure;
use Chiron\Filesystem\Filesystem;

use Chiron\Config\EventsConfig;
use Chiron\Event\EventDispatcher;
use Psr\EventDispatcher\EventDispatcherInterface;
use Chiron\Event\ListenerProvider;
use Psr\EventDispatcher\ListenerProviderInterface;

final class CoreServiceProvider implements ServiceProviderInterface
{
    public function register(BindingInterface $binder): void
    {
        $binder->mutation(InjectableConfigInterface::class, [InjectableConfigMutation::class, 'mutation']);
        $binder->mutation(ContainerAwareInterface::class, [ContainerAwareMutation::class, 'mutation']);

        $binder->singleton(Console::class, Closure::fromCallable([static::class, 'registerConsole']));
        $binder->singleton(Configure::class, Closure::fromCallable([static::class, 'registerConfigure']));
        $binder->singleton(Publisher::class, Closure::fromCallable([static::class, 'registerPublisher']));

        // TODO : améliorer ces deux bindings ???? surtout qu'on devrait binder ces classes au niveau du package chiron/core !!!!
        $binder->singleton(ListenerProvider::class, Closure::fromCallable([static::class, 'registerListenerProvider']));
        $binder->singleton(ListenerProviderInterface::class, \Chiron\Container\Reference::to(ListenerProvider::class)); // TODO : remplacer cette ligne par un ->alias()
        $binder->singleton(EventDispatcher::class);
        $binder->singleton(EventDispatcherInterface::class, EventDispatcher::class);
    }

    private static function registerConsole(Container $container, ConsoleConfig $config): Console
    {
        // TODO : éventuellement pour clarifier le code on pourrait insérer les commands directement dans le command loader et seulement ensuite faire le new Console($loader)
        $loader = new CommandLoader($container);
        $console = new Console($loader);

        // Init the console with the configured values.
        $console->setName($config->getName());
        $console->setVersion($config->getVersion());

        foreach ($config->getCommands() as $command) {
            // TODO : lever une ApplicationException si le getDefaultName n'est pas présent dans la classe command, ou si la constante NAME n'est pas définie, ou alors si le type de classe n'est pas une instanceof Symfony\Command::class
            // TODO : eventuellement utiliser un FactoryInterface pour créer la commande si on voit qu'on ne pourra pas la charge de maniére Lazy (cad qu'on n'a pas trouvé son nom dans NAME ou via le getDefaultName())
            $console->addCommand($command::getDefaultName(), $command);
        }

        // TODO : mettre ce bout de code dans un BootLoader !!!!
        // Insert the default application commands.
        $commands = [
            //\Chiron\Discover\Command\PackageDiscoverCommand::class,
            \Chiron\Command\AboutCommand::class,
            \Chiron\Command\CacheClearCommand::class,
            \Chiron\Command\DebugConfigCommand::class,
            \Chiron\Command\PublishCommand::class,
            //\Chiron\Command\ThanksCommand::class,
            \Chiron\Command\EventsCommand::class,
        ];

        foreach ($commands as $command) {
            $console->addCommand($command::getDefaultName(), $command);
        }

        return $console;
    }

    private static function registerConfigure(Directories $directories): Configure
    {
        // TODO : attention il faudrait gérer le cas ou le répertoire "config" n'existe pas, voir ce que ca donne comme erreurs !!!
        $path = $directories->get('@config');
        $loader = new PhpLoader($path);
        $configure = new Configure($loader);

        return $configure;
    }

    private static function registerPublisher(Directories $directories): Publisher
    {
        $publisher = new Publisher();

        // TODO : utiliser plutot $directories->get('@framework/config/app.php.dist') au lieu d'utiliser le chemin __DIR__ ???? ou éventuellement la constante public CHIRON_PATH qui est définie dans la classe Application::class !!!

        // copy the configuration file template from the package "config" folder to the user "config" folder.
        $publisher->add(__DIR__ . '/../../../config/console.php.dist', $directories->get('@config/console.php'));
        $publisher->add(__DIR__ . '/../../../config/events.php.dist', $directories->get('@config/events.php'));
        $publisher->add(__DIR__ . '/../../../config/services.php.dist', $directories->get('@config/services.php'));
        $publisher->add(__DIR__ . '/../../../config/settings.php.dist', $directories->get('@config/settings.php'));

        return $publisher;
    }

    // TODO : utiliser un FactoryInterface pour créer la classe de type ProviderInterface qui correspond au listener !!!!
    private static function registerListenerProvider(EventsConfig $config): ListenerProvider
    {
        $provider = new ListenerProvider();

        foreach ($config->getListeners() as $listener) {
            $provider->attach(resolve($listener)); // TODO : utiliser plutot la fonction FactoryInterface::build()
        }

        return $provider;
    }
}
