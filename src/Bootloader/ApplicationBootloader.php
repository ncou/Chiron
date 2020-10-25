<?php

declare(strict_types=1);

namespace Chiron\Bootloader;

use Chiron\Application;
use Chiron\Core\Container\Bootloader\AbstractBootloader;
use Chiron\Config\AppConfig;
use Chiron\Console\ConsoleDispatcher;
use Chiron\Http\SapiDispatcher;

final class ApplicationBootloader extends AbstractBootloader
{
    public function boot(Application $application, AppConfig $config): void
    {
        foreach ($config->getProviders() as $provider) {
            $application->addProvider(resolve($provider));
        }

        foreach ($config->getBootloaders() as $bootloader) {
            $application->addBootloader(resolve($bootloader));
        }

        foreach ($config->getDispatchers() as $dispatcher) {
            $application->addDispatcher(resolve($dispatcher));
        }
    }
}
