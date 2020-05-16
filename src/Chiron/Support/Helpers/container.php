<?php

declare(strict_types=1);

use Chiron\Boot\Directories;
use Chiron\Boot\Environment;
use Chiron\Container\Container;
use Psr\Container\ContainerExceptionInterface;

if (! function_exists('directory')) {
    /**
     * Get directory alias value. Uses application core from the current global scope.
     *
     * @param string $alias Directory alias, ie. "config".
     *
     * @return string
     */
    // TODO : ajouter un second paramétre de type string, qui correspondra à une partie de l'url à concaténer, par exemple : directory('runtime', 'logs/error.txt') ca donnera un résultat "xxxx/app/runtime/logs/error.txt"
    function directory(string $alias): string
    {
        return container(Directories::class)->get($alias);
    }
}

if (! function_exists('env')) {
    /**
     * Gets the value of an environment variable. Uses application core from the current global scope.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    function env(string $key, $default = null)
    {
        return container(Environment::class)->get($key, $default);
    }
}

if (! function_exists('container')) {
    /**
     * Resolve given alias in the container.
     *
     * @param string $alias Class name or alias.
     *
     * @throws RuntimeException
     *
     * @return mixed
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
