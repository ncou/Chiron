<?php

declare(strict_types=1);

use Chiron\Boot\Configure;
use Chiron\Boot\Directories;
use Chiron\Boot\Environment;
use Chiron\Config\ConfigInterface;
use Chiron\Container\Container;
use Psr\Container\ContainerExceptionInterface;

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

if (! function_exists('directory')) {
    /**
     * Get directory alias value.
     *
     * @param string $alias Directory alias, ie. "@config".
     *
     * @return string
     */
    function directory(string $alias): string
    {
        // TODO : utiliser la facade ???
        return container(Directories::class)->get($alias);
    }
}

if (! function_exists('env')) {
    /**
     * Gets the value of an environment variable.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    function env(string $key, $default = null)
    {
        // TODO : utiliser la facade ???
        return container(Environment::class)->get($key, $default);
    }
}

if (! function_exists('configure')) {
    /**
     * Get the specified configuration object.
     *
     * @param string      $section
     * @param string|null $subset
     *
     * @return \Chiron\Config\ConfigInterface
     */
    function configure(string $section, ?string $subset = null): ConfigInterface
    {
        // TODO : utiliser la facade ????
        return container(Configure::class)->getConfig($section, $subset);
    }
}

if (! function_exists('setting')) {
    /**
     * Get the specified value in the settings config.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    function setting(string $key)
    {
        $config = configure('settings');

        if (! $config->has($key)) {
            throw new InvalidArgumentException(sprintf('The provided settings key [%s] doesn\'t exists.', $key));
        }

        return $config->get($key);
    }
}
