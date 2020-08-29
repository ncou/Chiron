<?php

declare(strict_types=1);

namespace Chiron\Facade;

use Chiron\Container\Container;
use RuntimeException;

//https://github.com/lizhichao/one/blob/master/src/Facades/Facade.php

/**
 * You must override the function "getFacadeAccessor" in your class and return the Container alias key used to retrieve the service.
 */
// TODO : ajouter une méthode pour insérer un ContainerInterface dans cette classe et ne plus utiliser directement la fonction "container()" mais passer par l'objet qu'on aura injecté. Il faudra aussi prévoir une classe de boot pour injecter ce container. Genre ajouter un ContainerAwareInterface à cette classe. exemple :        https://github.com/laravel/framework/blob/1bbe5528568555d597582fdbec73e31f8a818dbc/src/Illuminate/Foundation/Bootstrap/RegisterFacades.php#L22
abstract class AbstractFacade extends AbstractFacadeProxy
{
    /**
     * Prevent the instanciation of the class. Use only static calls.
     */
    private function __construct()
    {
    }

    /**
     * getInstance.
     *
     * @param bool $forceNew
     *
     * @return mixed
     */
    // TODO : forcer le type de retour à "object" attention il faut une version minimale de PHP 7.3 pour utiliser cette notation !!!!
    // TODO : éventuellement faire un check is_object et lever une RuntimeException (ou exception consom si besoin) si le type obtenu depuis le container n'est pas le bon !!!! + ajouter dans la dochead le @throw RuntimeException par exemple.
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
