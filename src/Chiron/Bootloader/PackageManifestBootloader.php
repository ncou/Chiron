<?php

namespace Chiron\Bootloader;

use Chiron\Application;
use Chiron\Bootload\AbstractBootloader;
use Chiron\Container\FactoryInterface;
use Chiron\PackageManifest;

final class PackageManifestBootloader extends AbstractBootloader
{
    /**
     * Execute the providers & bootloaders classes found in the composer packages manifest.
     *
     * @param PackageManifest  $manifest
     * @param Application      $application
     * @param FactoryInterface $factory
     */
    // TODO : créer une fonction "factory()" dans le fichier function.php pour permettre d'initialiser les classes sans avoir à passer en paramétre un FactoryInterface !!!!
    public function boot(PackageManifest $manifest, Application $application, FactoryInterface $factory): void
    {
        foreach ($manifest->getProviders() as $provider) {
            if (class_exists($provider)) {
                $application->addProvider($factory->build($provider));
            }
        }

        foreach ($manifest->getBootloaders() as $bootloader) {
            if (class_exists($bootloader)) {
                $application->addBootloader($factory->build($bootloader));
            }
        }
    }
}
