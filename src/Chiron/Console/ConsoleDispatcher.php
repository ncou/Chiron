<?php

declare(strict_types=1);

namespace Chiron\Console;

use Chiron\Boot\Environment;
use Chiron\Http\DispatcherInterface;

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
     * @param Console $console
     */
    public function __construct(Console $console, Environment $env)
    {
        $this->console = $console;
        $this->env = $env;
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(): int
    {
        return $this->console->run();
    }

    /**
     * {@inheritdoc}
     */
    public function canDispatch(): bool
    {
        // only run in pure CLI more, ignore under RoadRunner
        return php_sapi_name() === 'cli' && $this->env->get('RR') === null;
    }
}
