<?php

declare(strict_types=1);

namespace Chiron\Bootloader;

use Chiron\Core\Container\Bootloader\AbstractBootloader;
use Chiron\Console\Console;
use Chiron\Filesystem\Filesystem;

// TODO : utiliser ce principe pour découvrir les classes de command automatiquement : https://github.com/cakephp/cakephp/blob/b6800b33e19f7204b500465e46eecd6fa30388b2/src/Console/CommandScanner.php

// TODO : il faudrait pas plutot utiliser la classe CommandLoader pour charger ces commandes ????
final class CommandBootloader extends AbstractBootloader
{
    // TODO : utiliser plutot un CommandLoader::class plutot que directement l'object Console::class
    public function boot(Filesystem $filesystem, Console $console): void
    {
        $path = __DIR__ . '/../Command/';
        $classPattern = '*Command.php';

        $files = $filesystem->find($path, $classPattern);

        foreach ($files as $file) {
            $class = 'Chiron\\Command\\' . $file->getBasename('.php');
            $console->addCommand($class::getDefaultName(), $class); // TODO : il faudrait surement faire une vérification sur le is_subclass_of($class, CommandInterface::class)) avant d'ajouter la classe à la console, ou alors faire ce test directement depuis la class Console::class lors de l'appel à la méthode addCommand !!!!
        }
    }

    // TODO : code à virer !
    public function boot_SAVE(Console $console): void
    {
        // TODO : utiliser un fichier interne de config pour charger les commandes internes au framework ????
        $commands = [
            \Chiron\Command\AboutCommand::class,
            \Chiron\Command\PackageDiscoverCommand::class,
            \Chiron\Command\PublishCommand::class,
            \Chiron\Command\CacheClearCommand::class,
            \Chiron\Command\DebugConfigCommand::class,

            // TODO : à déplacer dans un package d'encodage dédié ?
            //\Chiron\Command\EncryptKeyCommand::class,
            //\Chiron\Command\KeyGenerateCommand::class,
            //\Chiron\Command\KeyUpdateCommand::class, // TODO : à déplacer dans un package sur chiron/dotenv-bridge ????
        ];

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

        foreach ($commands as $command) {
            $console->addCommand($command::getDefaultName(), $command);
        }
    }
}
