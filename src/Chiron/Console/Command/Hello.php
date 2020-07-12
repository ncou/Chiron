<?php

declare(strict_types=1);

namespace Chiron\Console\Command;

use Chiron\Console\AbstractCommand;

class Hello extends AbstractCommand
{
    protected static $defaultName = 'hello:world';

    protected static $TOTO = 'toto value is toto';

    protected const TITI = 'titi is back';













    protected function configure()
    {
        $this->setDescription('Outputs "Hello World"');
    }

    public function perform()
    {
        //$this->writeln('Arguments dans le input : ' . (string) $this->input);

        $this->writeln('Hello World');
        //$this->line('FOO', 'error');

        //$this->output->title('FOOBAR');

        //$this->output->confirm('FOOBARBAZ ????', true);

        //throw new \RuntimeException("Test A Virer");

        return 0;
    }
}
