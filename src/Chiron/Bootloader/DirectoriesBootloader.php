<?php

declare(strict_types=1);

namespace Chiron\Bootloader;

use Chiron\Boot\Directories;
use Chiron\Bootload\AbstractBootloader;
use Chiron\Console\Config\ConsoleConfig;
use Chiron\Console\Console;
use Chiron\Framework;
use Chiron\Exception\ApplicationException;

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
    public function boot(Directories $directories): void
    {
        $directories->init(self::mapDirectories($this->paths));
        // insert the chiron framwork path for later use.
        $directories->set('framework', Framework::path());
        // some folders should be presents and writable.
        self::assertWritableDir($directories, ['@runtime', '@cache', '@logs']);
    }

    /**
     * Normalizes directory list and adds all required aliases.
     * Also enforce the correct order for the alias (ex : @root should be declared before @root/xxxx)
     *
     * @param array $paths
     *
     * @return array
     */
    private static function mapDirectories(array $paths): array
    {
        $aliases = self::normalizeAliases($paths);

        // ensure mandatory directory alias '@root' is defined by the user.
        if (! isset($aliases['@root'])) {
            throw new ApplicationException('Missing required directory alias "@root".');
        }

        // TODO : il faudrait pas ajouter un répertoire pour les logs ???? => https://github.com/spiral/app/blob/85705bb7a0dafd010a83fa4bcc7323b019d8dda3/app/src/Bootloader/LoggingBootloader.php#L29
        return array_merge([
            // root folders
            '@app'          => '@root/app',
            '@config'       => '@root/config',
            '@public'       => '@root/public',
            '@resources'    => '@root/resources',
            '@runtime'      => '@root/runtime',
            '@vendor'       => '@root/vendor',
            // ressources folders
            '@views'        => '@resources/views',
            // runtime folders
            '@cache'        => '@runtime/cache',
            '@logs'         => '@runtime/logs',
        ], $aliases);
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
                throw new ApplicationException('Directories paths aliases must be an associative array.');
            }
            // check if alias doesn't start with '@'
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
     * @param array $aliases
     */
    private static function assertWritableDir(Directories $directories, array $aliases): void
    {
        foreach ($aliases as $alias) {
            $path = $directories->get($alias);

            if (! is_dir($path)) {
                throw new ApplicationException(sprintf('Directory "%s" (%s) does\'t exist.', $alias, $path));
            }

            if (! is_writable($path)) {
                throw new ApplicationException(sprintf('Directory "%s" (%s) isn\'t writable.', $alias, $path));
            }
        }
    }
}
