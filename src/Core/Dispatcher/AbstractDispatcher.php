<?php

declare(strict_types=1);

namespace Chiron\Core\Dispatcher;

use Chiron\Injector\Injector;
use Closure;
use Psr\Container\ContainerInterface;

// TODO : créer dans le fichier functions.php une méthode "invoke()" qui serait un helper pour executer un new Injector()->call($callable), ca pourrait simplifier le code lorsqu'on souhaite executer/résoudre des callable avant de les executer. Ca éviterai aussi dans cette classe d'avoir la méthode construct avec le Container en paramétre, et de réduire la fonction dispatch à une seule ligne !!!!

/**
 * Allow Lazy loads services used as parameters for the 'perform()' function.
 */
// TODO : sortir le container du constructeur et utiliser le trait ContainerAwareTrait + ContainerAwareInterface, avec une mutation du Container qui injecterai automatiquement le container ????
abstract class AbstractDispatcher implements DispatcherInterface
{
    /** @var Injector */
    protected $injector;

    /** @var ContainerInterface */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    // TODO : il faudrait plutot lui passer en paramétre un InvokerInterface ou une classe Injector::class pour récupérer directement l'injector, car la variable container ne sert à rien !!!!
    // TODO : ou alors lui passer en paramétre un "Container" et donc on pourrait directement effectuer un ->call depuis ce container, plus besoin d'initialiser un Injector !!!!
    public function __construct(ContainerInterface $container)
    {
        $this->injector = new Injector($container);
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch()
    {
        // TODO : utiliser une facade pour accéder à l'objet "Injector" ? cela éviterai d'avoir une méthode __construct() dans cette classe !!!!
        return $this->injector->call(Closure::fromCallable([$this, 'perform']));
    }

    /**
     * {@inheritdoc}
     */
    abstract public function canDispatch(): bool;
}
