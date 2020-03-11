<?php

declare(strict_types=1);

namespace Chiron\Console;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application as SymfonyConsole;

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
