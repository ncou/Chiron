<?php

declare(strict_types=1);

namespace Chiron\Command;

use Chiron\Core\Command\AbstractCommand;
use Chiron\Filesystem\Filesystem;
use Chiron\Core\Publisher;
use Chiron\Core\Directories;
use Symfony\Component\Console\Input\InputOption;
use Chiron\Core\Exception\PublishException;
use Closure;

//https://github.com/laravelista/lumen-vendor-publish/blob/master/src/VendorPublishCommand.php
//https://github.com/illuminate/support/blob/master/ServiceProvider.php#L370

//https://github.com/spiral/framework/blob/e865a013af9b75b712192c477b80066abb02ec0d/src/Framework/Command/PublishCommand.php
//https://github.com/spiral/framework/blob/e865a013af9b75b712192c477b80066abb02ec0d/src/Framework/Module/Publisher.php

// TODO : passer les méthodes "perform" en protected pour chaque classe de type "Command"
final class PublishCommand extends AbstractCommand
{
    /**
     * @var Filesystem
     */
    private $filesystem;
    /**
     * @var Directories
     */
    private $directories;

    protected static $defaultName = 'publish';


    protected function configure(): void
    {
        $this->setDescription('Publish ressources.')
        ->addOption('force', 'f', InputOption::VALUE_NONE, 'Overwrite any existing files');
    }

    public function perform(Publisher $publisher, Filesystem $filesystem, Directories $directories): int
    {
        $this->filesystem = $filesystem;
        $this->directories = $directories;

        $publisher->setCallback(Closure::fromCallable([$this, 'status']));

        try{
            $publisher->publish($this->option('force'));
        } catch(PublishException $e){
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->success('Publishing complete.');

        return self::SUCCESS;
    }

    /**
     * Write a status message to the console.
     *
     * @param string $from
     * @param string $to
     * @param string $type
     */
    private function status(string $from, string $to, string $type)
    {
        $rootPath = $this->directories->get('@root');
        $from = $this->filesystem->relativePath($from, $rootPath);
        $to = $this->filesystem->relativePath($to, $rootPath);

        $this->line(
            sprintf('<info> ✔ Copied %s</info> <comment>[%s]</comment> <info>To</info> <comment>[%s]</comment>',
                $type,
                $from,
                $to)
        );
    }
}

