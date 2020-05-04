<?php

declare(strict_types=1);

namespace Chiron\Bootloader;

use Chiron\Application;
use Chiron\Bootload\BootloaderInterface;
use Chiron\Dispatcher\ConsoleDispatcher;
use Chiron\Dispatcher\RrDispatcher;
use Chiron\Dispatcher\SapiDispatcher;
use Chiron\Dispatcher\ReactDispatcher;
use Spiral\RoadRunner\PSR7Client;
use Chiron\Container\Container;

// TODO : on devrait pas créer une class "AbstractBootLoader" qui serai une abstract class et qui aurait une méthode getContainer, cad qui aurait dans le constructeur directement le container car on utilise souvent le container, ca éviterai de devoir le passer dans la méthode boot() !!!!
// TODO : classe à renommer en ApplicationBootloader !!!!
class DispatcherBootloader implements BootloaderInterface
{
    // TODO : éventuellement utiliser les fichiers de config console.php et http.php pour définir les dispatchers à utiliser !!!!
    // TODO : lui passer plutot un FactoryInterface en paramétre et non pas un container, ce qui permettrait de faire un "make()" pour créer les 3 classes de dispatcher !!!
    public function boot(Application $application, Container $container): void
    {
        // TODO : ne pas charger le dispatche RrDispatcher et SapiDispatcher dans le cas ou on est en mode console (PHP_SAPI === 'cli') cela libére de la mémoire !!!!
        // TODO : déplacer ce code dans la classe "Configurator" au moment on on va créer l'application ? Ca éviterai d'avoir cette classe "DispatcherBootloader" qui ne sert pas à grand chose.
        $application->addDispatcher($container->get(SapiDispatcher::class));
        $application->addDispatcher($container->get(ConsoleDispatcher::class));
        $application->addDispatcher($container->get(RrDispatcher::class));
        //$application->addDispatcher($container->get(ReactDispatcher::class));
    }
}
