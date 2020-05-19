<?php

declare(strict_types=1);

namespace Chiron\Bootloader;

use Chiron\Bootload\AbstractBootloader;
use Chiron\Console\Config\ConsoleConfig;
use Chiron\Console\Console;

class CommandBootloader extends AbstractBootloader
{
    public function boot(Console $console, ConsoleConfig $config): void
    {
        // TODO : améliorer le code pour utiliser plutot une classe de type "CommandLoader" pour charger les commandes depuis le fichier de config et en utilisant le container !!!!
        foreach ($config->getCommands() as $command) {
            $console->addCommand($command);
        }
    }
}
