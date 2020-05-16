<?php

declare(strict_types=1);

namespace Chiron\Bootloader;

use Chiron\Application;
use Chiron\Config\AppConfig;
use Chiron\Bootload\BootloaderInterface;
use Chiron\Dispatcher\ConsoleDispatcher;
use Chiron\Dispatcher\RrDispatcher;
use Chiron\Dispatcher\SapiDispatcher;
use Chiron\Dispatcher\ReactDispatcher;
use Spiral\RoadRunner\PSR7Client;
use Chiron\Container\Container;

// TODO : on devrait pas créer une class "AbstractBootLoader" qui serai une abstract class et qui aurait une méthode getContainer, cad qui aurait dans le constructeur directement le container car on utilise souvent le container, ca éviterai de devoir le passer dans la méthode boot() !!!!
class ApplicationBootloader implements BootloaderInterface
{
    // TODO : lui passer plutot un FactoryInterface en paramétre et non pas un container, ce qui permettrait de faire un "make()" pour créer les classes des dispatchers !!!
    public function boot(Application $application, AppConfig $config, Container $factory): void
    {
        /*
        $application->addDispatcher($factory->get(SapiDispatcher::class));
        $application->addDispatcher($factory->get(ConsoleDispatcher::class));
        $application->addDispatcher($factory->get(RrDispatcher::class));
        $application->addDispatcher($factory->get(ReactDispatcher::class));
        */


        foreach ($config->getProviders() as $provider) {
            $application->addProvider($factory->get($provider));
        }

        foreach ($config->getBootloaders() as $bootloader) {
            $application->addBootloader($factory->get($bootloader));
        }

        foreach ($config->getDispatchers() as $dispatcher) {
            $application->addDispatcher($factory->get($dispatcher));
        }
    }
}
