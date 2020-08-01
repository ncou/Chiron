<?php

declare(strict_types=1);

namespace Chiron\Bootloader;

use Chiron\Bootload\AbstractBootloader;
use Chiron\Console\Console;

final class CommandBootloader extends AbstractBootloader
{
    // TODO : utiliser un fichier interne de config pour charger les commandes internes au framework ????
    private $commands = [
        // TODO : à déplacer dans un package d'encodage dédié ?
        \Chiron\Console\Command\EncryptKeyCommand::class,

        \Chiron\Console\Command\AboutCommand::class,
        \Chiron\Console\Command\PackageDiscoverCommand::class,
        \Chiron\Console\Command\PublishCommand::class,

        // TODO : charger ces commandes uniquement si il y a un RouterInterface de présent (cad un class_exist === true) ???? ou alors tester avec un $container->has('RouterInterface')
        \Chiron\Console\Command\RouteListCommand::class,
        \Chiron\Console\Command\ServeCommand::class,

        \Chiron\Console\Command\CacheClearCommand::class,
    ];

    public function boot(Console $console): void
    {
        // TODO : attention si il y a des bootloaders chargés via le packagemanifest qui ajoutent une commande dans la console, si cette commande utilise le même nom que les commandes par défaut  définies ci dessous, elles vont être écrasées !!!! faut il faire un test dans cette classe si la command est déjà définie dans la console on ne l'ajoute pas (éventuellement on léve une ApplicationException si la commande est déjà définie en indiquant qu'on ne peux pas l'écraser !!!!) ????? EXEMPLE ci dessous :
        /*
                if (! $console->has(xxxxx::getDefaultName())) {
                    $console->addCommand(xxxxx::getDefaultName(), xxxxx::class);
                }

                OU

                if ($console->has(xxxxx::getDefaultName())) {
                    Throw new ApplicationException('Internal command "XXXX" can't be overriden');
                }
        */

        // TODO : code à améliorer !!!!
        // TODO : charger certaines commandes que dans le cas ou on est en mode http ou en mode console !!!! (exemple pour la commande Serve et RouteList qui ne servent pas en mode application 100% console) !!!!
        //$console->addCommand(Hello::getDefaultName(), Hello::class);
        //$console->addCommand(VersionCommand::getDefaultName(), VersionCommand::class);
        //$console->addCommand(RuntimeDirCommand::getDefaultName(), RuntimeDirCommand::class);

        foreach ($this->commands as $command) {
            $console->addCommand($command::getDefaultName(), $command);
        }
    }
}
