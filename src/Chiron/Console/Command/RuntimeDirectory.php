<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Console\Sequence;

use Spiral\Boot\DirectoriesInterface;
use Spiral\Files\FilesInterface;
use Symfony\Component\Console\Output\OutputInterface;

//https://github.com/yiisoft/yii-demo/blob/master/src/Installer.php#L16

/**
 * Creates runtime directory or/and ensure proper permissions for it.
 */
final class RuntimeDirectory
{
    /** @var FilesInterface */
    private $filesystem;

    /** @var DirectoriesInterface */
    private $directories;

    /**
     * @param FilesInterface       $files
     * @param DirectoriesInterface $directories
     */
    public function __construct(FilesInterface $filesystem, DirectoriesInterface $directories)
    {
        $this->filesystem = $filesystem;
        $this->directories = $directories;
    }

    /**
     * @param OutputInterface $output
     */
    public function ensure(OutputInterface $output): void
    {
        $output->write('Verifying runtime directory... ');

        $runtimeDirectory = $this->directories->get('runtime');

        if (! $this->filesystem->exists($runtimeDirectory)) {
            $this->filesystem->ensureDirectory($runtimeDirectory);
            $output->writeln('<comment>created</comment>');

            return;
        }
        $output->writeln('<info>exists</info>');

        foreach ($this->filesystem->files($runtimeDirectory) as $filename) {
            try {
                $this->filesystem->setPermissions($filename, FilesInterface::RUNTIME);
                $this->filesystem->setPermissions(dirname($filename), FilesInterface::RUNTIME);
            } catch (\Throwable $e) {
                // @codeCoverageIgnoreStart
                $output->writeln(
                    sprintf(
                        '<fg=red>[errored]</fg=red> `%s`: <fg=red>%s</fg=red>',
                        $this->filesystem->relativePath($filename, $runtimeDirectory),
                        $e->getMessage()
                    )
                );

                continue;
                // @codeCoverageIgnoreEnd
            }

            if ($output->isVerbose()) {
                $output->writeln(
                    sprintf(
                        '<fg=green>[updated]</fg=green> `%s`',
                        $this->filesystem->relativePath($filename, $runtimeDirectory)
                    )
                );
            }
        }

        $output->writeln('Runtime directory permissions were updated.');
    }
}
