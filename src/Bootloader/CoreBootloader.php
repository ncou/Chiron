<?php

declare(strict_types=1);

namespace Chiron\Bootloader;

use Chiron\Application;
use Chiron\Bootload\AbstractBootloader;
use Chiron\Config\CoreConfig;
use Chiron\Container\Container;

// TODO : classe à virer !!!!!

final class CoreBootloader extends AbstractBootloader
{
    // TODO : lui passer plutot un FactoryInterface en paramétre et non pas un container, ce qui permettrait de faire un "make()" pour créer les classes des providers/bootloaders/commands...etc !!!
    public function boot(Application $application, CoreConfig $coreConfig, Container $factory): void
    {
        foreach ($coreConfig->getProviders() as $provider) {
            $application->addProvider($factory->get($provider));
        }

        foreach ($coreConfig->getBootloaders() as $bootloader) {
            $application->addBootloader($factory->get($bootloader));
        }
    }
}
