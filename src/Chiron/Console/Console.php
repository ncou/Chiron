<?php

declare(strict_types=1);

namespace Chiron\Console;

use Chiron\Invoker\Invoker;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application as SymfonyConsole;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use LogicException;

class Console
{
    private $container;
    private $application;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->application = $this->getSymfonyConsole();
    }

    public function getSymfonyConsole(): SymfonyConsole
    {
        /*
        if ($this->application !== null) {
            return $this->application;
        }*/

        $console = new SymfonyConsole();

        //$console->setCatchExceptions(false);
        $console->setAutoExit(false);

        return $console;
    }

    public function run(): int
    {
        return $this->application->run();
    }

    public function addCommand(string $className): void
    {
        $command = $this->container->get($className);

        if ($command instanceof AbstractCommand) {
            $command->setContainer($this->container);
        }

        $this->application->add($command);
    }

}
