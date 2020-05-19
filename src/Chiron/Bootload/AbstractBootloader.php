<?php

declare(strict_types=1);

namespace Chiron\Bootload;

use Chiron\Invoker\Invoker;
use Psr\Container\ContainerInterface;

abstract class AbstractBootloader implements BootloaderInterface
{
    public function bootload(ContainerInterface $container): void
    {
        $invoker = new Invoker($container);

        // TODO : lever une exception si la méthode boot n'est pas présente !!!!
        // TODO : il faudrait surement faire un try/catch autour de la méthode call, car si la méthode boot n'existe pas une exception sera retournée. Une fois le catch fait il faudra renvoyer une new BootloadException($e->getMessage()), pour convertir le type d'exception (penser à mettre le previous exception avec la valeur $e).
        $invoker->call([$this, 'boot']);
    }
}
