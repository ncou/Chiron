<?php

declare(strict_types=1);

namespace Chiron\Console\Command;

use Chiron\Console\AbstractCommand;
use Chiron\Console\ExitCode;
use Chiron\Boot\Filesystem;
use Chiron\PublishableCollection;
use Symfony\Component\Console\Input\InputOption;

final class PublishCommand extends AbstractCommand
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    protected static $defaultName = 'publish';

    /**
     * Create a new command instance.
     *
     * @param  Filesystem  $files
     * @return void
     */
    public function __construct(Filesystem $filesystem)
    {
        parent::__construct();

        $this->filesystem = $filesystem;
    }

    protected function configure()
    {
        $this->setDescription('Publish ressources.')
        ->addOption('force', 'f', InputOption::VALUE_NONE, 'Overwrite any existing files');
    }

    // TODO : réfléchir si on passe directement le $files dans le constructeur pour déplacer l'appel à la méthode normalizePath dans la méthode "status()" histoire que le code soit plus lisible
    public function perform(PublishableCollection $publishable): int
    {

/*
        $this->hr();
        $this->newLine();

        $this->listing(['Copied successfull', 'Foo', 'Bar']);

        $this->getOutput()->write('<info>toto</>');

        $this->getOutput()->write('<error>toto</>');

        $this->getOutput()->write('<comment>toto</>');

        $this->newLine();
        $this->getOutput()->write('<fg=default;bg=default> // </>titi');
        $this->newLine();

        $this->getOutput()->write('<default>toto</>');
        $this->getOutput()->write('<fg=white;bg=red>toto</>');

        $this->getOutput()->write('<blue>toto</>');
        $this->getOutput()->write('<green>toto</>');

        $this->newLine();
        //$this->getOutput()->writeln(['foo' => 'a', 'bar', 'baz']);
        $this->text(['foo', 'bar', 'baz']);

        $this->newLine();
        $this->alert('the roof is on fire');
        $this->newLine();
        $this->alert2('the roof is on fire');
        $this->newLine();
        $this->alert3('the roof is on fire');
        $this->newLine();

        $this->write($this->time('123456'));

        $this->newLine();
*/


        foreach ($publishable as $from => $to) {
            if ($this->filesystem->isDirectory($from)) {
                $this->status($from, $to, 'Directory');
                $this->publishDirectory($from, $to);
            } elseif ($this->filesystem->isFile($from)) {
                $this->status($from, $to, 'File');
                $this->publishFile($from, $to);
            } else {
                $this->error("Can't locate path: <{$from}>");
            }
        }

        return ExitCode::OK;
    }

    /**
     * Publish the directory to the given directory.
     *
     * @param  string  $from
     * @param  string  $to
     * @return void
     */
    public function publishDirectory(string $from, string $to)
    {
        foreach ($this->filesystem->files($from) as $file) {
            $this->publishFile($file, $to . '/' . $this->filesystem->basename($file));
        }
    }

    /**
     * Publish the file to the given path.
     *
     * @param  string  $from
     * @param  string  $to
     * @return void
     */
    public function publishFile(string $from, string $to)
    {
        if (! $this->filesystem->exists($to) || $this->option('force')) {
            $this->createParentDirectory(dirname($to));
            $this->filesystem->copy($from, $to);
        }
    }

    /**
     * Create the directory to house the published files if needed.
     *
     * @param  string  $directory
     * @return void
     */
    private function createParentDirectory(string $directory)
    {
        if (! $this->filesystem->isDirectory($directory)) {
            $this->filesystem->makeDirectory($directory, 0755, true);
        }
    }

    /**
     * Write a status message to the console.
     *
     * @param  string  $from
     * @param  string  $to
     * @param  string  $type
     * @return void
     */
    private function status(string $from, string $to, string $type)
    {
        $from = str_replace(directory('root'), '', $this->filesystem->normalizePath($from));
        $to = str_replace(directory('root'), '', $this->filesystem->normalizePath($to));

        $this->line('<info> ✔ Copied '.$type.'</info> <comment>['.$from.']</comment> <info>To</info> <comment>['.$to.']</comment>');
    }
}
