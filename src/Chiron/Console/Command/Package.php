<?php

declare(strict_types=1);

namespace Chiron\Console\Command;

use Chiron\Console\AbstractCommand;
use Chiron\PackageManifest;

class Package extends AbstractCommand
{
    protected static $defaultName = 'package:discover';

    protected function configure()
    {
        $this->setDescription('Package discover.');
    }

    public function perform(PackageManifest $manifest): int
    {
        /*
                if (!file_exists($autoloadFile = $this->config->get('vendor-dir').'/autoload.php')) {
                    throw new \RuntimeException(sprintf('Please run "composer install" before running this command: "%s" not found.', $autoloadFile));
                }
        */

        $manifest->clear();
        $manifest->build();

        /*
                $output->write('Verifying runtime directory... ');

                $runtimeDirectory = $this->dirs->get('runtime');

                if (!$this->files->exists($runtimeDirectory)) {
                    $this->files->ensureDirectory($runtimeDirectory);
                    $output->writeln('<comment>created</comment>');
                    return;
                }
                $output->writeln('<info>exists</info>');


                foreach ($this->files->getFiles($runtimeDirectory) as $filename) {
                    try {
                        $this->files->setPermissions($filename, FilesInterface::RUNTIME);
                        $this->files->setPermissions(dirname($filename), FilesInterface::RUNTIME);
                    } catch (\Throwable $e) {
                        // @codeCoverageIgnoreStart
                        $output->writeln(
                            sprintf(
                                '<fg=red>[errored]</fg=red> `%s`: <fg=red>%s</fg=red>',
                                $this->files->relativePath($filename, $runtimeDirectory),
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
                                $this->files->relativePath($filename, $runtimeDirectory)
                            )
                        );
                    }
                }

                $output->writeln('Runtime directory permissions were updated.');
        */

        return 0;
    }
}
