<?php

namespace Chiron\Service\Bootloader;

use Chiron\Application;
use Chiron\Core\Container\Bootloader\AbstractBootloader;
use Chiron\Discover\PackageManifest;
use Chiron\Service\ServiceManager;

//https://github.com/top-think/framework/blob/4de6f58c5e12a1ca80c788887b5208a6705f85d3/src/think/initializer/RegisterService.php

final class PackageManifestBootloader extends AbstractBootloader
{
    /**
     * Add the services providers & bootloaders found in the composer file (extra section).
     *
     * @param PackageManifest $manifest
     * @param ServiceManager  $services
     */
    public function boot(PackageManifest $manifest, ServiceManager $services): void
    {
        foreach ($manifest->config('providers') as $provider) {
            if (class_exists($provider)) {
                $services->addProvider($provider);
            }
        }

        foreach ($manifest->config('bootloaders') as $bootloader) {
            if (class_exists($bootloader)) {
                $services->addBootloader($bootloader);
            }
        }
    }
}
