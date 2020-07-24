<?php

declare(strict_types=1);

namespace Chiron\Bootloader;

use Chiron\Application;
use Chiron\Bootload\AbstractBootloader;
use Chiron\Config\AppConfig;
use Chiron\Config\CoreConfig;
use Chiron\Container\Container;

final class ApplicationBootloader extends AbstractBootloader
{
    // TODO : lui passer plutot un FactoryInterface en paramétre et non pas un container, ce qui permettrait de faire un "make()" pour créer les classes des providers/bootloaders/commands...etc !!!
    public function boot(Application $application, AppConfig $appConfig, Container $factory): void
    {
        foreach ($appConfig->getProviders() as $provider) {
            $application->addProvider($factory->get($provider));
        }

        foreach ($appConfig->getBootloaders() as $bootloader) {
            $application->addBootloader($factory->get($bootloader));
        }

        foreach ($appConfig->getDispatchers() as $dispatcher) {
            $application->addDispatcher($factory->get($dispatcher));
        }
    }
}
