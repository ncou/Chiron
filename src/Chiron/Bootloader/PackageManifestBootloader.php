<?php

namespace Chiron\Bootloader;

use Chiron\Bootload\AbstractBootloader;
use Chiron\Application;
use Chiron\Container\Container;
use Chiron\PackageManifest;

final class PackageManifestBootloader extends AbstractBootloader
{
    // TODO : lui passer aussi un objet Factory pour permettre de convertir les classename de string en new instance.
    public function boot(PackageManifest $manifest, Application $application, Container $factory): void
    {
        //die(var_dump($manifest->getProviders()));

        // register the providers / aliases / bootloaders found in the composer packages manifest.
        foreach ($manifest->getProviders() as $provider) {
            // TODO : à finir de coder et tester !!!!
            $application->addProvider($factory->get($provider));
        }

        foreach ($manifest->getAliases() as $alias) {
            // TODO : à finir de coder et tester !!!!
            $application->addAlias($factory->get($alias));
        }

        foreach ($manifest->getBootloaders() as $bootloader) {
            // TODO : à finir de coder et tester !!!!
            $application->addBootloader($factory->get($bootloader));
        }

        foreach ($manifest->getCommands() as $command) {
            // TODO : à finir de coder et tester !!!!
            $application->addCommand($factory->get($command));
        }
    }
}
