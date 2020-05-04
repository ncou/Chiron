<?php

namespace Chiron\Bootloader;

//use Chiron\Http\Psr\Response;
use Chiron\Bootload\BootloaderInterface;
use Chiron\PackageManifest;
use Chiron\Bootload\Configurator;

class PackageManifestBootloader implements BootloaderInterface
{
    public function boot(PackageManifest $manifest, Configurator $configurator)
    {
        // register the providers / aliases / bootloaders found in the composer packages manifest.
        foreach ($manifest->providers() as $provider) {
            // TODO : à finir de coder et tester !!!!
            //$configurator->addProvider($provider);
        }

        foreach ($manifest->aliases() as $alias) {
            // TODO : à finir de coder et tester !!!!
            $configurator->addAlias($alias);
        }

        foreach ($manifest->bootloaders() as $bootloader) {
            // TODO : à finir de coder et tester !!!!
            $configurator->addBootloader($bootloader);
        }

        foreach ($manifest->commands() as $command) {
            // TODO : à finir de coder et tester !!!!
            $configurator->addCommand($command);
        }
    }
}
