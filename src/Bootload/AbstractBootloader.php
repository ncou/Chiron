<?php

declare(strict_types=1);

namespace Chiron\Bootload;

use Chiron\Injector\Injector;
use Closure;
use Psr\Container\ContainerInterface;

abstract class AbstractBootloader implements BootloaderInterface
{
    // TODO : stocker le container dans une variable protected de la classe ce qui permettrait d'y accéder via un $this->container. Voir meême créer une méthode getContainer. Faire la même chose pour le invoker ? si on souhaite par exemple executer un sous executable de la classe par exemple la méthode boot() pourrait executer la méthode bootA() et ensuite bootB() en cascade.
    // TODO : créer une méthode protected 'boot()' qui retournerait une exception pour indiquer que cette méthode n'est pas implémentée dans la classe mére ? (cad 'overidden' en anglais). Faire la méme chose pour les classes AbstractCommand et AbstractServiceProvider ????
    public function bootload(ContainerInterface $container): void
    {
        $injector = new Injector($container);

        // TODO : ajouter un \Closure::fromCallable() car la méthode boot peut être protected, voir private !!! (idem pour la classe AbstractCommand !!!!)
        // TODO : lever une exception si la méthode boot n'est pas présente !!!!
        // TODO : il faudrait surement faire un try/catch autour de la méthode call, car si la méthode boot n'existe pas une exception sera retournée. Une fois le catch fait il faudra renvoyer une new BootloadException($e->getMessage()), pour convertir le type d'exception (penser à mettre le previous exception avec la valeur $e).
        $injector->call(Closure::fromCallable([$this, 'boot']));
    }

    // TODO : créer une méthode protected 'boot' dans cette classe qui léve une exception, donc si l'utilisateur n'a pas fait un override de cette méthode boot dans sa classe c'est la méthode ici qui prendra le relais et donc lévera une exception. Faire la même chose pour les classes abstraites de command/config...etc
    /*
    protected function boot()
    {
        throw new \LogicException('You need to define the "boot" method in you class');

    }*/
}
