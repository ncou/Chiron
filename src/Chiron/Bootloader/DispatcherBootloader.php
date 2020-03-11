<?php

declare(strict_types=1);

namespace Chiron\Bootloader;

use Chiron\Views\TemplateRendererInterface;
use Chiron\Container\Container;
use Chiron\Boot\DirectoriesInterface;
use Chiron\Views\Config\ViewsConfig;
use Chiron\Bootload\BootloaderInterface;
use Chiron\Container\BindingInterface;
use Chiron\Application;
use Chiron\Http\SapiDispatcher;
use Chiron\Http\RrDispatcher;
use Chiron\Console\ConsoleDispatcher;
use Spiral\RoadRunner\PSR7Client;

class DispatcherBootloader implements BootloaderInterface
{
    //public function boot(Application $application, SapiDispatcher $sapiDispatcher, ConsoleDispatcher $consoleDispatcher, RrDispatcher $rrDispatcher): void
    public function boot(Application $application, SapiDispatcher $sapiDispatcher, ConsoleDispatcher $consoleDispatcher): void
    {
        $application->addDispatcher($sapiDispatcher);
        $application->addDispatcher($consoleDispatcher);

/*
        if (class_exists(PSR7Client::class)) {
            $application->addDispatcher($rrDispatcher);
        }
*/

    }
}
