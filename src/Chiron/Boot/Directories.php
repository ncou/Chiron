<?php

declare(strict_types=1);

namespace Chiron\Boot;

use InvalidArgumentException;

/**
 * Manage application directories set.
 */
// TODO : permettre d'utiliser les helpers ArrayAccess pour faire un truc du genre "$directories['config']"
final class Directories implements DirectoriesInterface
{
    /** @var array */
    private $directories = [];
    /**
     * @param array $directories
     */
    public function __construct(array $directories)
    {
        foreach ($directories as $name => $directory) {
            $this->set($name, $directory);
        }
    }
    /**
     * {@inheritdoc}
     */
    public function set(string $name, string $path): DirectoriesInterface
    {
        $path = str_replace(['\\', '//'], '/', $path);
        $this->directories[$name] = rtrim($path, '/') . '/';

        return $this;
    }
    /**
     * {@inheritdoc}
     */
    public function get(string $name): string
    {
        if (! $this->has($name)) {
            throw new InvalidArgumentException("Undefined directory '{$name}'");
        }

        return $this->directories[$name];
    }
    /**
     * {@inheritdoc}
     */
    public function has(string $name): bool
    {
        return array_key_exists($name, $this->directories);
    }
    /**
     * {@inheritdoc}
     */
    // TODO : renommer cette méthode en "all()" ????
    public function getAll(): array
    {
        return $this->directories;
    }
}
