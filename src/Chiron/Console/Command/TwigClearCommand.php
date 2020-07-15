<?php

declare(strict_types=1);

namespace Chiron\Console\Command;

use Chiron\Filesystem\Filesystem;
use Chiron\Console\AbstractCommand;
use Chiron\Console\ExitCode;
use Chiron\PublishableCollection;
use Symfony\Component\Console\Input\InputOption;
use Chiron\Views\Config\TwigConfig;

//https://github.com/narrowspark/framework/blob/81f39d7371715ee20aa888a8934c36c536e3d69e/src/Viserio/Provider/Twig/Command/CleanCommand.php

// This class only work if the cache parameter is a string value (absolute path to the cache folder).
// TODO : utiliser le mot "clean" plutot que clear ????
final class TwigClearCommand extends AbstractCommand
{
    protected static $defaultName = 'twig:clear';

    protected function configure(): void
    {
        $this->setDescription('Clean the Twig cache folder.');
    }

    public function perform(Filesystem $filesystem, TwigConfig $config): int
    {
        $cacheDir = $config->get('options.cache');

        // The cache value defined in the Twig options could be : false (for no cache) / string (absolute path to the cache folder) / CacheInterface::class (if defined by the user)
        if (! is_string($cacheDir)) {
            $this->error('Twig cache option is not defined as an absolute path, so it can\'t be cleaned.');
        }

        // TODO : ajouter un try catch des \Throwable et afficher une erreur si c'est le cas ????
        $deleted = $filesystem->deleteDirectory($cacheDir);

        if ($deleted === false) {
            $this->error('Twig cache failed to be cleaned.');

            return 1;
        }

        $this->success('Twig cache cleaned.');

        return 0;
    }
}
