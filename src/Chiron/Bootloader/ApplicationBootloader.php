<?php

declare(strict_types=1);

namespace Chiron\Bootloader;

use Chiron\Application;
use Chiron\Bootload\AbstractBootloader;
use Chiron\Config\AppConfig;
use Chiron\Container\FactoryInterface;
use Chiron\Dispatcher\ConsoleDispatcher;
use Chiron\Dispatcher\SapiDispatcher;


final class ApplicationBootloader extends AbstractBootloader
{
    public function boot(Application $application, AppConfig $appConfig, FactoryInterface $factory): void
    {
        // the framework is built around the console so the ConsoleDispatcher is needed.
        $application->addDispatcher($factory->build(ConsoleDispatcher::class));
        // TODO : déplacer cet ajout du dispatcher dans le futur package "http" comme on a fait pour le package Roadrunner !!!!
        $application->addDispatcher($factory->build(SapiDispatcher::class));

        foreach ($appConfig->getProviders() as $provider) {
            $application->addProvider($factory->build($provider));
        }

        foreach ($appConfig->getBootloaders() as $bootloader) {
            $application->addBootloader($factory->build($bootloader));
        }

        foreach ($appConfig->getDispatchers() as $dispatcher) {
            $application->addDispatcher($factory->build($dispatcher));
        }
    }
}
