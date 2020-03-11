<?php

namespace Chiron\Bootloader;

//use Chiron\Http\Psr\Response;
use Chiron\Bootload\BootloaderInterface;
use Chiron\PackageManifest;

class PackageManifestBootloader implements BootloaderInterface
{
    public function boot(PackageManifest $manifest)
    {
        // register the providers / aliases / bootloaders found in the composer packages manifest.
        foreach ($manifest->providers() as $provider) {
            //TODO : faire le register
        }

        foreach ($manifest->aliases() as $alias) {
            //TODO : faire le register
        }

        foreach ($manifest->bootloader() as $bootloader) {
            //TODO : faire le register
        }
    }
}
