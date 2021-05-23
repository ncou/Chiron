<?php

namespace Chiron\Bootloader;

use Chiron\Application;
use Chiron\Core\Container\Bootloader\AbstractBootloader;
use Chiron\Composer\PackageManifest;
use Chiron\Service\ServiceManager;

//https://github.com/top-think/framework/blob/4de6f58c5e12a1ca80c788887b5208a6705f85d3/src/think/initializer/RegisterService.php

final class PackageManifestBootloader extends AbstractBootloader
{
    /**
     * Add the services providers & bootloaders found in the composer packages manifest.
     *
     * @param PackageManifest  $manifest
     * @param ServiceManager      $services
     */
    public function boot(PackageManifest $manifest, ServiceManager $services): void
    {
        foreach ($manifest->getProviders() as $provider) {
            if (class_exists($provider)) {
                $services->addProvider($provider);
            }
        }

        foreach ($manifest->getBootloaders() as $bootloader) {
            if (class_exists($bootloader)) {
                $services->addBootloader($bootloader);
            }
        }
    }
}
