<?php

declare(strict_types=1);

namespace Chiron\Command;

use Chiron\Core\Command\AbstractCommand;
use Chiron\Filesystem\Filesystem;
use Chiron\Core\Publisher;
use Chiron\Core\Directories;
use Symfony\Component\Console\Input\InputOption;
use Chiron\Core\Exception\PublishException;
use Closure;

// DOCUMENTATION : https://stillat.com/blog/2016/12/07/laravel-artisan-vendor-command-the-vendorpublish-command

//https://github.com/laravelista/lumen-vendor-publish/blob/master/src/VendorPublishCommand.php
//https://github.com/illuminate/support/blob/master/ServiceProvider.php#L370

//https://github.com/spiral/framework/blob/e865a013af9b75b712192c477b80066abb02ec0d/src/Framework/Command/PublishCommand.php
//https://github.com/spiral/framework/blob/e865a013af9b75b712192c477b80066abb02ec0d/src/Framework/Module/Publisher.php

// TODO : passer les méthodes "perform" en protected pour chaque classe de type "Command"
final class PublishCommand extends AbstractCommand
{
    /**
     * @var Filesystem
     */
    private $filesystem;
    /**
     * @var Directories
     */
    private $directories;

    protected static $defaultName = 'publish';


    protected function configure(): void
    {
        $this->setDescription('Publish ressources.')
        ->addOption('force', 'f', InputOption::VALUE_NONE, 'Overwrite any existing files');
    }

    public function perform(Publisher $publisher, Filesystem $filesystem, Directories $directories): int
    {
        $this->filesystem = $filesystem;
        $this->directories = $directories;

        $publisher->setCallback(Closure::fromCallable([$this, 'status']));

        try {
            $publisher->publish($this->option('force'));
        } catch(PublishException $e){
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->success('Publishing complete.');

        return self::SUCCESS;
    }

    /**
     * Write a status message to the console.
     *
     * @param string $from
     * @param string $to
     * @param string $type
     */
    // TODO : ne pas afficher le $type, il suffit d'aficher une ligne seulement lors de la copie d'un fichier, on s'en fiche de la création d'un "Directory"
    // TODO : afficher plus ou moins d'infos selon le niveau de debug.
    // TODO : utiliser un compteur (variable de classe qu'on passerait à la closure via l'instruction use) qui serait incrémenté pour afficher à la fin de la commande le nombre de fichiers copiés ???
    private function status(string $from, string $to, string $type)
    {
        $rootPath = $this->directories->get('@root');
        $from = $this->filesystem->relativePath($from, $rootPath);
        $to = $this->filesystem->relativePath($to, $rootPath);

        // TODO : utiliser plutot ce bout de code ??? Ca évitera de mettre dans les paramétres de la méthode perform une instance Directories et Filesystem, donc une signature de méthode plus propre !!!!
        //$from = str_replace(directory('@root'), '', $from);
        //$to = str_replace(directory('@root'), '', $to);

        // TODO : Attention on n'est pas sur que la console support le UTF8, utiliser plutot un "tiret" à la place du signe checked.
        $this->line(
            sprintf('<info> ✔ Copied %s </info><comment>%s</comment> <info>To</info> <comment>%s</comment>',
                $type,
                $from,
                $to)
        );
    }
}

