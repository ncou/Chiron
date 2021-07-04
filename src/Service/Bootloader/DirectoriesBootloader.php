<?php

declare(strict_types=1);

namespace Chiron\Service\Bootloader;

use Chiron\Core\Directories;
use Chiron\Core\Container\Bootloader\AbstractBootloader;
use Chiron\Core\Exception\DirectoryException;
use Chiron\Framework;
use Chiron\Views\TemplateRendererInterface;
use Chiron\Filesystem\Filesystem;

// TODO : passer les méthodes "boot()" en protected !!!! ou alors si ce n'est pas le cas, il faut supprimer le Closure::fromCallable qu'on utilise avant d'appeller le invoker dans la méthode bootload() car ce wrapping ne sert que dans le cas ou la méthode à appeller est private ou protected !!!!
final class DirectoriesBootloader extends AbstractBootloader
{
    /** @var array */
    private $paths;

    /**
     * @param array $paths
     */
    public function __construct(array $paths)
    {
        $this->paths = $paths;
    }

    /**
     * @param Directories $directories
     */
    public function boot(Directories $directories, Filesystem $filesystem): void
    {
        // Use default directories structure if needed.
        $directories->init(self::mapDirectories($this->paths));
        // Insert the chiron framwork path for later use.
        $directories->set('framework', Framework::path()); // TODO : pas vraiment utile, à virer !!!!

        // Some folders should be presents and writables.
        //self::assertWritableDir($directories, ['@runtime', '@cache']); // TODO : il faudrait plutot faire un Filesystem->ensureDirectoryExist(xxxx) pour forcer la création du répertoire si il n'existe pas !!!!

        // TODO : eventuellement lever une exception si on n'arrive pas à créer ces répertoires !!! cad faire un try/catch autour de ces 2 appels !!!
        $filesystem->ensureDirectoryExists($directories->get('@runtime'));
        $filesystem->ensureDirectoryExists($directories->get('@cache'));
    }

    /**
     * Normalizes directory list and adds all required aliases.
     *
     * @param array $paths
     *
     * @return array
     */
    private static function mapDirectories(array $paths): array
    {
        $aliases = self::normalizeAliases($paths);

        // TODO : je pense que root / public et runtime sont les 3 répertoires obligatoires, mais à vérifier !!!!
        // ensure mandatory directory alias '@root' is defined by the user.
        if (! isset($aliases['@root'])) {
            throw new DirectoryException('Missing required directory alias "@root".');
        }

        // TODO : il faudrait pas ajouter un répertoire pour les logs ???? => https://github.com/spiral/app/blob/85705bb7a0dafd010a83fa4bcc7323b019d8dda3/app/src/Bootloader/LoggingBootloader.php#L29
        // TODO : faire le ménage on doit pas avoir besoin de tous ces répertoires !!! notamment le répertoire '@public' qui ne sert à rien lorsqu'on fait une application en ligne de commandes !!!!
        // TODO : ajouter de maniére séparé le chemin vers vendor !!!
        $default = [
            '@app'          => '@root/app/',
            '@config'       => '@root/config/',
            '@public'       => '@root/public/',
            '@resources'    => '@root/resources/',
            '@runtime'      => '@root/runtime/',
            '@vendor'       => '@root/vendor/', // Assume a standard Composer directory structure unless specified.
            '@cache'        => '@runtime/cache/',
        ];

        // if a view engine is installed, we add the default 'views' folder.
        //https://github.com/spiral/framework/blob/master/src/Framework/Bootloader/Views/ViewsBootloader.php#L47
        if (interface_exists(TemplateRendererInterface::class)) {
            $default['@views'] = '@resources/views/';
        }

        return array_merge($default, $aliases);
    }

    /**
     * Enforce the alias directory name start with an '@' character.
     *
     * @param array $paths
     *
     * @return array
     */
    private static function normalizeAliases(array $paths): array
    {
        $aliases = [];

        foreach ($paths as $alias => $path) {
            if (! is_string($alias)) {
                throw new DirectoryException('Directories paths aliases must be an associative array.');
            }
            // check if alias doesn't start with '@'
            // TODO : utiliser la méthode Str::startWith(xxxx)
            if (strncmp($alias, '@', 1) !== 0) {
                $aliases['@' . $alias] = $path;
            } else {
                $aliases[$alias] = $path;
            }
        }

        return $aliases;
    }

    /**
     * @param Directories $directories
     * @param array       $aliases
     */
    private static function assertWritableDir(Directories $directories, array $aliases): void
    {
        foreach ($aliases as $alias) {
            $path = $directories->get($alias);

            if (! is_dir($path)) {
                throw new DirectoryException(sprintf('Directory "%s" (%s) does\'t exist.', $alias, $path));
            }

            if (! is_writable($path)) {
                throw new DirectoryException(sprintf('Directory "%s" (%s) isn\'t writable.', $alias, $path));
            }
        }
    }
}
