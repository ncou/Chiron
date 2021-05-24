<?php

declare(strict_types=1);

namespace Chiron\Service\Bootloader;

use Chiron\Application;
use Chiron\Core\Container\Bootloader\AbstractBootloader;
use Chiron\Config\ServicesConfig;
use Chiron\Service\ServiceManager;

final class ServicesBootloader extends AbstractBootloader
{
    /**
     * Add the services providers & bootloaders found in the services config file.
     *
     * @param ServiceManager      $services
     * @param ServicesConfig  $config
     */
    // TODO : faire aussi un test class_exist avant de faire le addXXXX ? comme ce qu'on fait dans la classe PackageManifestBootloader ????
    public function boot(ServiceManager $services, ServicesConfig $config): void
    {
        foreach ($config->getProviders() as $provider) {
            $services->addProvider($provider);
        }

        foreach ($config->getBootloaders() as $bootloader) {
            $services->addBootloader($bootloader);
        }
    }
}
