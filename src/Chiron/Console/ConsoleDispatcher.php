<?php

declare(strict_types=1);

namespace Chiron\Console;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Chiron\Http\DispatcherInterface;
use Chiron\Boot\Environment;
use Throwable;

/**
 * Manages Console commands and exception. Lazy loads console service.
 */
final class ConsoleDispatcher implements DispatcherInterface
{

    /** @var Console */
    private $console;
    /** @var Environment */
    private $env;

    /**
     * @param Console   $console
     */
    public function __construct(Console $console, Environment $env)
    {
        $this->console = $console;
        $this->env = $env;
    }

    /**
     * @inheritdoc
     */
    public function dispatch(): int
    {
        return $this->console->run();
    }

    /**
     * @inheritdoc
     */
    public function canDispatch(): bool
    {
        // only run in pure CLI more, ignore under RoadRunner
        return php_sapi_name() === 'cli' && $this->env->get('RR') === null;
    }
}
