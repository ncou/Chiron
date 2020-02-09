<?php

declare(strict_types=1);

use Chiron\Container\Container;
use Chiron\Boot\DirectoriesInterface;
use Chiron\Boot\EnvironmentInterface;
use Psr\Container\ContainerExceptionInterface;

/**
 * Resolve given alias in current IoC scope.
 *
 * @param string $alias Class name or alias.
 * @return object|null
 *
 * @throws ScopeException
 */
/*
function spiral(string $alias)
{
    if (ContainerScope::getContainer() === null) {
        throw new ScopeException('Container scope was not set.');
    }
    try {
        return ContainerScope::getContainer()->get($alias);
    } catch (ContainerExceptionInterface $e) {
        throw new ScopeException($e->getMessage(), $e->getCode(), $e);
    }
}*/

// TODO : fonction à renommer en "chiron()" ????
if (!function_exists('container')) {
    /**
     * Resolve given alias in the container.
     *
     * @param string $alias Class name or alias.
     * @return object|null
     *
     */
    function container(string $alias, bool $forceNew = false)
    {
        //return (Container::$instance)->get($alias);

        $container = Container::$instance;

        if ($container === null) {
            throw new RuntimeException('Container instance was not set.');
        }

        try {
            return $container->get($alias, $forceNew);
        } catch (ContainerExceptionInterface $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
    }
}

if (!function_exists('directory')) {
    /**
     * Get directory alias value. Uses application core from the current global scope.
     *
     * @param string $alias Directory alias, ie. "config".
     * @return string
     */
    // TODO : ajouter un second paramétre de type string, qui correspondra à une partie de l'url à concaténer, par exemple : directory('runtime', 'logs/error.txt') ca donnera un résultat "xxxx/app/runtime/logs/error.txt"
    function directory(string $alias): string
    {
        return container(DirectoriesInterface::class)->get($alias);
    }
}

if (!function_exists('env')) {
    /**
     * Gets the value of an environment variable. Uses application core from the current global scope.
     *
     * @param string $key
     * @param mixed  $default
     * @return mixed
     */
    function env(string $key, $default = null)
    {
        return container(EnvironmentInterface::class)->get($key, $default);
    }
}
