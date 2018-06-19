<?php

declare(strict_types=1);

/**
 * Chiron Framework (https://chiron.com).
 *
 * @see      https://github.com/slimphp/Slim
 *
 * @copyright Copyright (c) 2011-2017 Josh Lockhart
 * @license   https://github.com/slimphp/Slim/blob/3.x/LICENSE.md (MIT License)
 */

namespace Chiron\Handler;

//use Closure;

// TODO : regarder aussi ici : https://github.com/silexphp/Silex/blob/master/src/Silex/CallbackResolver.php

use Chiron\Routing\Strategy\RouteInvocationStrategy;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DeferredRequestHandler implements RequestHandlerInterface
{
    private $callable;

    /** @var ContainerInterface */
    private $container;

    private $strategy;

    /**
     * DeferredMiddleware constructor.
     *
     * @param callable|string    $callable
     * @param ContainerInterface $container used only if the $callable is a string
     */
    public function __construct($callable, ContainerInterface $container = null)
    {
        // TODO : lever une exception si le paramétre $callable n'est pas "is_string ou is_callable"
        $this->callable = $callable;
        $this->container = $container;
        // TODO : stocker la stratégie directement dans le container !!!!! il faudrait faire un serviceProvider qui fait un register dans le container
        $this->strategy = new RouteInvocationStrategy();
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $callable = $this->resolveCallable($this->callable);

        // TODO : ce bind est aussi fait au niveau de la fonction Application->map() !!!! c'est pas bon car c'est fait en double
        /*
        if ($callable instanceof \Closure) {
            $callable = $callable->bindTo($this->container);
        }*/

        // TODO : vérifier l'utilité !!!!!
        /*
                if (is_array($callable) && is_callable([$callable[0], 'setContainer']) && $this->container instanceof ContainerInterface) {
                    $callable[0]->setContainer($this->container);
                }
        */

        /*
                if (method_exists($callable, 'setContainer') && $this->container instanceof ContainerInterface) {
                    $callable->setContainer($this->container);
                }
        */

        //$args = func_get_args();
        //return call_user_func_array($callable, $args);

        // execute the callable with the request as parameter (+ the strategy using reflection to match request attribute with the callable parameters)
        return call_user_func_array($this->strategy, [$callable, $request]);
        //return $this->strategy($callable, $request);
        //return call_user_func($callable, $request);
    }

    /**
     * Resolve toResolve into a closure that that the router can dispatch.
     *
     * If toResolve is of the format 'class@method', then try to extract 'class'
     * from the container otherwise instantiate it and then dispatch 'method'.
     *
     * @param callable|string $toResolve
     *
     * @throws RuntimeException if the callable does not exist
     * @throws RuntimeException if the callable is not resolvable
     *
     * @return callable
     */
    //https://github.com/slimphp/Slim/blob/4.x/Slim/CallableResolver.php#L47
    // TODO : lui passer uniquement une string en paramétre et ne pas appeller cette fonction en amont si le type est callable !!!!
    private function resolveCallable($toResolve)
    {
        $resolved = $toResolve;
        if (! is_callable($toResolve) && is_string($toResolve)) {
            $class = $toResolve;
            $method = '__invoke';
            // check for chiron callable as "class@method"
            //$callablePattern = '!^([^\:]+)\:([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)$!'; // TODO : il est possible de simplifier cela avec l'expression : '^([^:]+):([^:]+)$'
            //$callablePattern = '!^([^#]+)#([^#]+)$!';
            $callablePattern = '^([^@]+)@([^@]+)$';
            if (preg_match($callablePattern, $toResolve, $matches)) {
                $class = $matches[1];
                $method = $matches[2];
            }
            // TODO : faire plutot un check de ce genre, car le container est initialisé en interne donc toujours une instanceof "ContainerInterface" : if ($this->container && $this->container->has($class)) {
            if ($this->container instanceof ContainerInterface && $this->container->has($class)) {
                $resolved = [$this->container->get($class), $method];
            } else {
                if (! class_exists($class)) {
                    throw new \RuntimeException(sprintf('Class %s does not exist', $class));
                }
                $resolved = [new $class($this->container), $method];
            }
        }
        if (! is_callable($resolved)) {
            throw new \InvalidArgumentException(sprintf(
                '(%s) is not resolvable',
                is_array($resolved) || is_object($resolved) || is_null($resolved) ? json_encode($resolved) : $resolved
            ));
        }
        // TODO : vérifier l'utilité de ce bindTo !!!!!!! normalement ce n'est pas nécessaire car le container ajoute le $this lorsqu'on ajoute un service.
        // TODO : attention ce bout de code doit surement écraser le bind qu'on fait dans la fonction Router->map() quand on bindTo la closure avec $app. Ici on va surement défaire ce bindTo pour en refaire un avec $container
        /*
        if ($this->container instanceof ContainerInterface && $resolved instanceof \Closure) {
            $resolved = $resolved->bindTo($this->container);
        }
        */
        return $resolved;
    }
}
