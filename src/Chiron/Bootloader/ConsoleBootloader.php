<?php

declare(strict_types=1);

namespace Chiron\Bootloader;

use Chiron\Bootload\AbstractBootloader;
use Chiron\Console\Config\ConsoleConfig;
use Chiron\Console\Console;

final class ConsoleBootloader extends AbstractBootloader
{
    // TODO : il faudra peut etre passer par l'objet Application::class pour faire un addCommand(). A minima il faudra surement déplacer les "Commands" du fichier console.php vers app.php
    public function boot(Console $console, ConsoleConfig $config): void
    {
        // TODO : améliorer le code pour utiliser plutot une classe de type "CommandLoader" pour charger les commandes depuis le fichier de config et en utilisant le container !!!!
        foreach ($config->getCommands() as $command) {
            $console->addCommand($command);
        }
    }
}
