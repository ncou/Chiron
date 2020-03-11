<?php

declare(strict_types=1);

namespace Chiron\Bootloader;

use Chiron\Bootload\BootloaderInterface;
use Chiron\Console\Config\ConsoleConfig;
use Chiron\Console\Console;

class CommandBootloader implements BootloaderInterface
{
    public function boot(Console $console, ConsoleConfig $config): void
    {
        foreach ($config->getCommands() as $command) {
            $console->addCommand($command);
        }
    }
}
