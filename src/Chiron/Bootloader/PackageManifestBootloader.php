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
        // TODO : vérifier si la classe existe bien avant de faire un build() sinon on va avoir une erreur !!! cela peut arriver je pense dans le cas ou on uninstall un package et que le fichier de cache est donc invalide !!!
        foreach ($manifest->getProviders() as $provider) {
            $application->addProvider($factory->build($provider));
        }

        // TODO : vérifier si la classe existe bien avant de faire un build() sinon on va avoir une erreur !!! cela peut arriver je pense dans le cas ou on uninstall un package et que le fichier de cache est donc invalide !!!
        foreach ($manifest->getBootloaders() as $bootloader) {
            $application->addBootloader($factory->build($bootloader));
        }
    }
}
