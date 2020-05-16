<?php

namespace Chiron\Bootloader;

//use Chiron\Http\Psr\Response;
use Chiron\Bootload\BootloaderInterface;
use Chiron\PackageManifest;
use Chiron\Bootload\Configurator;

class PackageManifestBootloader implements BootloaderInterface
{
    // TODO : il faudra plutot lui passer un object Application::class plutot que le Configurator::class en paramétre de fonction pour ajouter les commands/mutations/providers/booloaders
    // TODO : lui passer aussi un objet Factory pour permettre de convertir les classename de string en new instance.
    public function boot(PackageManifest $manifest, Configurator $configurator)
    {
        // register the providers / aliases / bootloaders found in the composer packages manifest.
        foreach ($manifest->getProviders() as $provider) {
            // TODO : à finir de coder et tester !!!!
            //$configurator->addProvider($provider);
        }

        foreach ($manifest->getAliases() as $alias) {
            // TODO : à finir de coder et tester !!!!
            $configurator->addAlias($alias);
        }

        foreach ($manifest->getBootloaders() as $bootloader) {
            // TODO : à finir de coder et tester !!!!
            $configurator->addBootloader($bootloader);
        }

        foreach ($manifest->getCommands() as $command) {
            // TODO : à finir de coder et tester !!!!
            $configurator->addCommand($command);
        }
    }
}
