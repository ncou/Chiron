<?php

declare(strict_types=1);

namespace Chiron\Command;

use Chiron\Console\AbstractCommand;
use Chiron\Config\SecurityConfig;
use Chiron\Filesystem\Filesystem;
use Chiron\Core\Support\Security;
use Symfony\Component\Console\Input\InputOption;
use Chiron\Core\Environment;

final class KeyUpdateCommand extends AbstractCommand
{
    protected static $defaultName = 'key:update';

    protected function configure()
    {
        $this
            ->setDescription('Generate new security key.')
            ->addOption('mount', 'm', InputOption::VALUE_REQUIRED, 'Mount security key into given .env file');
    }

    protected function perform(Environment $environment, Filesystem $filesystem): int
    {
        $filepath = $this->option('mount');

        if ($filepath === null) {
            $this->error('The option value for "--mount" is required.');

            return self::FAILURE;
        }

        if ($filesystem->missing($filepath)) {
            $this->error(sprint('Unable to find file [%s].', $filepath));

            return self::FAILURE;
        }

        $updated = $this->updateEnvironmentFile($environment, $filesystem, $filepath);

        if ($updated) {
            $this->success('Security key has been updated.');
        } else {
            $this->warning('Security key was not updated!');
        }

        return self::SUCCESS;
    }

    /**
     * Update the environment file with the new security key.
     *
     * @param  Environment $environment
     * @param  Filesystem  $filesystem
     * @param  string      $filepath
     *
     * @return bool Return if the file has been updated or not.
     */
    private function updateEnvironmentFile(Environment $environment, Filesystem $filesystem, string $filepath): bool
    {
        $oldKey = $environment->get('APP_KEY');
        $newKey = Security::generateKey(SecurityConfig::KEY_BYTES_SIZE, false);

        $content = preg_replace(
            sprintf('/^APP_KEY=%s/m', $oldKey),
            'APP_KEY=' . $newKey,
            $filesystem->read($filepath),
            1,
            $counter
        );

        // The variable $counter is filled with the number of replacements done.
        if ($counter === 1) {
            $filesystem->write($filepath, $content);

            if ($this->isVerbose()) {
                $this->sprintf("<info>New key:</info> <fg=cyan>%s</fg=cyan>\n", $newKey);
            }

            return true;
        }

        return false;
    }
}
