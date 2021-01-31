<?php

declare(strict_types=1);

namespace Chiron\Bootloader;

use Chiron\Core\Container\Bootloader\AbstractBootloader;
use Chiron\Config\ConsoleConfig;
use Chiron\Console\Console;

// TODO : virer cette classe et la remplacer par un ServiceProvider qui se chargerai de faire la même chose que ce code ci dessous !!!!!
final class ConsoleBootloader extends AbstractBootloader
{
    // TODO : il faudra peut etre passer par l'objet Application::class pour faire un addCommand(). A minima il faudra surement déplacer les "Commands" du fichier console.php vers app.php
    public function boot(Console $console, ConsoleConfig $config): void
    {
        $console->setName($config->getName());
        $console->setVersion($config->getVersion());

        foreach ($config->getCommands() as $command) {
            // TODO : lever une ApplicationException si le getDefaultName n'est pas présent dans la classe command, ou si la constante NAME n'est pas définie, ou alors si le type de classe n'est pas une instanceof Symfony\Command::class
            // TODO : eventuellement utiliser un FactoryInterface pour créer la commande si on voit qu'on ne pourra pas la charge de maniére Lazy (cad qu'on n'a pas trouvé son nom dans NAME ou via le getDefaultName())
            $console->addCommand($command::getDefaultName(), $command);
        }
    }
}
