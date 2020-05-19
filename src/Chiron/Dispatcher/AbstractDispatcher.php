<?php

declare(strict_types=1);

namespace Chiron\Dispatcher;

use Chiron\Invoker\Invoker;
use Closure;
use Psr\Container\ContainerInterface;

// TODO : créer dans le fichier functions.php une méthode "invoke()" qui serait un helper pour executer un new Invoker()->call($callable), ca pourrait simplifier le code lorsqu'on souhaite executer/résoudre des callable avant de les executer. Ca éviterai aussi dans cette classe d'avoir la méthode construct avec le Container en paramétre, et de réduire la fonction dispatch à une seule ligne !!!!

/**
 * Allow Lazy loads services used as parameters for the 'perform()' function.
 */
// TODO : sortir le container du constructeur et utiliser le trait ContainerAwareTrait + ContainerAwareInterface, avec une mutation du Container qui injecterai automatiquement le container ????
abstract class AbstractDispatcher implements DispatcherInterface
{
    /** @var Invoker */
    protected $invoker;

    /** @var ContainerInterface */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->invoker = new Invoker($container);
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch()
    {
        return $this->invoker->call(Closure::fromCallable([$this, 'perform']));
    }

    /**
     * {@inheritdoc}
     */
    abstract public function canDispatch(): bool;
}
