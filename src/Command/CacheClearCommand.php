<?php

declare(strict_types=1);

namespace Chiron\Command;

use Chiron\Core\Directories;
use Chiron\Core\Console\AbstractCommand;
use Chiron\Filesystem\Filesystem;

// TODO : passer les méthodes "perform" en protected pour chaque classe de type "Command"
// TODO : utiliser le mot "clean" plutot que clear ????
final class CacheClearCommand extends AbstractCommand
{
    protected static $defaultName = 'cache:clear';

    protected function configure(): void
    {
        $this->setDescription('Clean application runtime cache.');
    }

    public function perform(Filesystem $filesystem, Directories $directories): int
    {
        $cacheDir = $directories->get('@cache');

        // TODO : vérifier si ce cas peut arriver !!! je ne pense pas car il y a une vérification au démarrage de l'application pour vérifier que ce répertoire est writable.
        // TODO : ce bout de code ne servira à rien si on léve une errezur dans la méthode deleteDirectory si la cible n'est pas un répertoire (ce qui sera la cas si le répertoire n'existe pas !) et le try catch affichera le message qui va bien.
        if ($filesystem->missing($cacheDir)) {
            $this->writeln('Cache directory is missing, no cache to be cleaned.');

            return 0;
        }

        // TODO : ajouter un try catch des \Throwable et afficher une erreur si c'est le cas ????
        $deleted = $filesystem->deleteDirectory($cacheDir, true);

        /*
                try {
                    $filesystem->deleteDirectory($cacheDir);
                } catch (\Throwable $e) {
                    // @codeCoverageIgnoreStart
                    $this->sprintf(
                        "<fg=red>[ERROR] %s</fg=red>\n",
                        $e->getMessage()
                    );
                    // @codeCoverageIgnoreEnd
                }
        */

        if ($deleted === false) {
            $this->error('Application runtime cache failed to be cleaned.');

            return 1;
        }

        if ($this->isVerbose()) {
            $this->sprintf(
                "<fg=green>[cleaned]</fg=green> `%s`\n",
                $cacheDir
            );
        }

        $this->success('Application runtime cache cleaned.');

        return 0;
    }
}
