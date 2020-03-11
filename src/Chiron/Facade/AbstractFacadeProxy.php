<?php

declare(strict_types=1);

namespace Chiron\Facade;

use Chiron\Container\Container;

/**
 * You must override the function "getFacadeAccessor" in your class and return the Container alias key used to retrieve the service.
 */
abstract class AbstractFacadeProxy
{
    /**
     * Prevent the instanciation of the class. Use only static calls.
     */
    private function __construct()
    {
    }

    /**
     * Handle dynamic, static calls to the object.
     *
     * @param string $method    The method name.
     * @param array  $arguments The arguments of method call.
     *
     * @return mixed
     */
    public static function __callStatic(string $method, array $arguments)
    {
        $instance = static::getInstance();

        return $instance->$method(...$arguments);
    }

    abstract public static function getInstance();
}
