<?php

declare(strict_types=1);

namespace Chiron\Console\Command;

use Chiron\Filesystem\Filesystem;
use Chiron\Boot\Directories;
use Chiron\Boot\Environment;
use Chiron\Console\AbstractCommand;
use Chiron\Encrypter\Config\EncrypterConfig;
use Chiron\Support\Security;
use Symfony\Component\Console\Input\InputOption;
use Chiron\Application;
use Chiron\Framework;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Chiron\Bootloader\EnvironmentBootloader;

use Chiron\Boot\Path;

/**
 * A console command to display information about the current installation.
 */
final class AboutCommand extends AbstractCommand
{
    protected static $defaultName = 'about';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setDescription('Displays information about the current project');
    }

    public function perform(Directories $directories, Environment $environement): int
    {
        $rows = [
            ['<info>Framework</>'],
            new TableSeparator(),
            ['Name', Framework::name()],
            ['Version', Framework::version()],
            ['Path', Framework::path()],
            //['Long-Term Support', 4 === Kernel::MINOR_VERSION ? 'Yes' : 'No'],
            //['End of maintenance', Kernel::END_OF_MAINTENANCE . (self::isExpired(Kernel::END_OF_MAINTENANCE) ? ' <error>Expired</>' : '')],
            //['End of life', Kernel::END_OF_LIFE . (self::isExpired(Kernel::END_OF_LIFE) ? ' <error>Expired</>' : '')],
            new TableSeparator(),
            ['<info>Application</>'],
            new TableSeparator(),
            //['Type', \get_class($kernel)],
            //['Environment', $kernel->getEnvironment()],
            //['Debug', $kernel->isDebug() ? 'true' : 'false'],
            //['Charset', $kernel->getCharset()],

            // TODO : afficher tous les dossiers de l'application. cad ceux qui sont listés dans l'objet Directories::class

            ['Cache directory', self::formatPath($directories->get('@cache'), $directories->get('@root')) . ' (<comment>' . self::formatFileSize($directories->get('@cache')) . '</>)'],
            new TableSeparator(),
            ['<info>PHP</>'],
            new TableSeparator(),
            // TODO : déplacer ces informations dans une classe System et on utiliserai des méthodes du style getPhpVersion() ...etc pour avoir les infos. idem pour l'OS.
            ['Version', PHP_VERSION],
            ['Architecture', (PHP_INT_SIZE * 8) . ' bits'],
            ['Intl locale', class_exists('Locale', false) && \Locale::getDefault() ? \Locale::getDefault() : 'n/a'],
            ['Timezone', date_default_timezone_get() . ' (<comment>' . (new \DateTime())->format(\DateTime::W3C) . '</>)'],
            //['OPcache', \extension_loaded('Zend OPcache') && filter_var(ini_get('opcache.enable'), FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false'],
            //['APCu', \extension_loaded('apcu') && filter_var(ini_get('apc.enabled'), FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false'],
            ['Xdebug', \extension_loaded('xdebug') ? 'true' : 'false'],
        ];

        // TODO : utiliser plutot un foreach et la méthode $table->addRow();
        if ($dotenv = $environement->get(EnvironmentBootloader::DOTENV)) {
            $rows = array_merge($rows, [
                new TableSeparator(),
                ['<info>Environment (.env)</>'],
                new TableSeparator(),
            ], array_map(function ($value, $name) {
                return [$name, $value];
            }, $dotenv, array_keys($dotenv)));
        }

        $table = $this->table([], $rows);

        //$table->addRow();

        $table->render();


        return self::SUCCESS;
    }

    // TODO : utiliser la méthode Path::relativePath() ou Filesystem::relativePath()
    private static function formatPath(string $path, string $baseDir): string
    {
        return Path::getRelativePath2($baseDir, $path);
        //return preg_replace('~^' . preg_quote($baseDir, '~') . '~', './', $path);
    }

    //https://github.com/cakephp/filesystem/blob/master/Folder.php#L658
    //https://github.com/JBZoo/Utils/blob/5a2b7c01f48318585212fa9876c8c48c8817d974/src/FS.php#L222
    // TODO : déplacer cette fonction dans la classe filesystem::class ou path::class ?
    private static function formatFileSize(string $path): string
    {
        if (is_file($path)) {
            $size = filesize($path) ?: 0;
        } else {
            $size = 0;
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS | \RecursiveDirectoryIterator::FOLLOW_SYMLINKS)) as $file) {
                $size += $file->getSize();
            }
        }

        //https://github.com/JBZoo/Utils/blob/5a2b7c01f48318585212fa9876c8c48c8817d974/src/FS.php#L273
        return Helper::formatMemory($size);
    }
}
