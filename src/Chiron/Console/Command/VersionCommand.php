<?php

declare(strict_types=1);

namespace Chiron\Console\Command;

use Chiron\Filesystem\Filesystem;
use Chiron\Console\AbstractCommand;
use Chiron\Console\ExitCode;
use Chiron\Encrypter\Config\EncrypterConfig;
use Chiron\Support\Security;
use Symfony\Component\Console\Input\InputOption;
use Chiron\Application;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


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

        return ExitCode::OK;
    }
}
