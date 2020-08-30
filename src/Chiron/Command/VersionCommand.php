<?php

declare(strict_types=1);

namespace Chiron\Command;

use Chiron\Application;
use Chiron\Console\AbstractCommand;

/**
 * Print out the version of Chiron in use.
 */
final class VersionCommand extends AbstractCommand
{
    protected static $defaultName = 'version';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setDescription('Print out the version of Chiron in use');
    }

    public function perform(): int
    {
        $this->write(Application::VERSION);

        return self::SUCCESS;
    }
}
