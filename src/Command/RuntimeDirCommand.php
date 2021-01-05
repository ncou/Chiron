<?php

declare(strict_types=1);

namespace Chiron\Command;

use Chiron\Core\Directories;
use Chiron\Core\Console\AbstractCommand;
use Chiron\Filesystem\Filesystem;

//https://github.com/yiisoft/yii-demo/blob/master/src/Installer.php#L16

// permissions : 0666 pour spiral  / 0777 pour yiisoft / 0775 ou 0755 je ne sais plus pour symfony

/**
 * Creates runtime directory or/and ensure proper permissions for it.
 */
// TODO : classe à virer car on a un probléme si on essaye de créer le dossier, les bootloader de l'application s'executent et ils ne trouvent pas le Router lors de l'instanciation de la classe RouteCollector, et cela car le package discovery ne s'execute pas car le répertoire runtime n'est pas créé, donc impossible de faire marcher cette commande !!!!
final class RuntimeDirCommand extends AbstractCommand
{
    /** @var Filesystem */
    private $filesystem;

    /** @var Directories */
    private $directories;

    protected static $defaultName = 'runtime';

    // readable, writable and executable by all users
    private const RUNTIME_PERMISSION = 0777;

    /**
     * @param Filesystem  $files
     * @param Directories $directories
     */
    public function __construct(Filesystem $filesystem, Directories $directories)
    {
        parent::__construct();

        $this->filesystem = $filesystem;
        $this->directories = $directories;
    }

    protected function configure(): void
    {
        $this->setDescription('Creates runtime directory or/and ensure proper permissions for it.');
    }

    public function perform(): int
    {
        $this->write('Verifying runtime directory... ');

        $runtimeDirectory = $this->directories->get('@runtime');

        if (! $this->filesystem->exists($runtimeDirectory)) {
            $this->filesystem->ensureDirectory($runtimeDirectory);
            $this->writeln('<comment>created</comment>');

            return self::SUCCESS;
        }

        $this->writeln('<info>exists</info>');

        foreach ($this->filesystem->files($runtimeDirectory) as $filename) {
            $filename = $filename->getRealPath();

            try {
                $this->filesystem->setPermissions($filename, self::RUNTIME_PERMISSION);
                $this->filesystem->setPermissions(dirname($filename), self::RUNTIME_PERMISSION);
            } catch (\Throwable $e) {
                $this->writeln(
                    sprintf(
                        '<fg=red>[errored]</fg=red> `%s`: <fg=red>%s</fg=red>',
                        $this->filesystem->relativePath($filename, $runtimeDirectory),
                        $e->getMessage()
                    )
                );

                continue;
            }

            if ($this->isVerbose()) {
                $this->writeln(
                    sprintf(
                        '<fg=green>[updated]</fg=green> `%s`',
                        $this->filesystem->relativePath($filename, $runtimeDirectory)
                    )
                );
            }
        }

        $this->writeln('Runtime directory permissions were updated.');

        return self::SUCCESS;
    }
}
