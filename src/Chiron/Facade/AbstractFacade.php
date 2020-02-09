<?php

declare(strict_types=1);

namespace Chiron\Facade;

use Chiron\Container\Container;
use RuntimeException;

/**
 * You must override the function "getFacadeAccessor" in your class and return the Container alias key used to retrieve the service.
 */
abstract class AbstractFacade extends AbstractFacadeProxy
{
    /**
     * Prevent the instanciation of the class. Use only static calls
     */
    private function __construct()
    {
    }

    /**
     * getInstance
     *
     * @param bool $forceNew
     *
     * @return mixed
     */
    // TODO : forcer le type de retour à "object" attention il faut une version minimale de PHP 7.3 pour utiliser cette notation !!!!
    // TODO : éventuellement faire un check is_object et lever une RuntimeException si le type obtenu depuis le container n'est pas le bon !!!!
    public static function getInstance(bool $forceNew = false)
    {
        return container(static::getFacadeAccessor(), $forceNew);
    }

    /**
     * Get the registered name of the component in the container.
     *
     * @return string
     */
    abstract protected static function getFacadeAccessor(): string;
}
