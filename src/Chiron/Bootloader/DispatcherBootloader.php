<?php

declare(strict_types=1);

namespace Chiron\Bootloader;

use Chiron\Application;
use Chiron\Bootload\BootloaderInterface;
use Chiron\Console\ConsoleDispatcher;
use Chiron\Http\RrDispatcher;
use Chiron\Http\SapiDispatcher;
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
