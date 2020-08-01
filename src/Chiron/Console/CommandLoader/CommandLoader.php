<?php

declare(strict_types=1);

namespace Chiron\Console\CommandLoader;

use Chiron\Console\AbstractCommand;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\Console\Exception\CommandNotFoundException;

/**
 * Loads commands from a PSR-11 container.
 */
//  TODO : créer une méthode set($key, $value) pour ajouter une command à la liste $this->commandMap, et virer le parametre $commandMap du container, ou alors l'initialiser pas défaut à []
class CommandLoader implements CommandLoaderInterface
{
    /** @var ContainerInterface */
    private $container;

    /** @var array An array with command names as keys and service ids as values */
    private $commandMap = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function set(string $name, string $command): void
    {
        $this->commandMap[$name] = $command;
    }

    /**
     * {@inheritdoc}
     */
    public function get($name): Command
    {
        if (! $this->has($name)) {
            throw new CommandNotFoundException(sprintf('Command "%s" does not exist.', $name));
        }

        $command = $this->container->get($this->commandMap[$name]);

        // TODO : Faire un test si la classe à un ContainerAwareInterface dans ce cas on injecte le container. Ou alors utiliser une mutation du container pour injecter le container automatiquement ?
        if ($command instanceof AbstractCommand) {
            $command->setContainer($this->container);
        }

        return $command;
    }

    /**
     * {@inheritdoc}
     */
    public function has($name): bool
    {
        return isset($this->commandMap[$name]) && $this->container->has($this->commandMap[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function getNames(): array
    {
        return array_keys($this->commandMap);
    }
}
