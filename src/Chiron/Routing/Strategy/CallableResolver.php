<?php

declare(strict_types=1);

namespace Chiron\Routing\Strategy;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Chiron\Routing\Route;
use Chiron\Http\Psr\Response;
use Closure;

use Psr\Container\ContainerInterface;
use RuntimeException;
use InvalidArgumentException;

/**
 * This class resolves a string of the format 'class@method' into a closure
 * that can be dispatched.
 */
final class CallableResolver implements CallableResolverInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @param ContainerInterface|null $container
     */
    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container;
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
    public function resolve($toResolve): callable
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
                    throw new \RuntimeException(sprintf('Callable %s does not exist', $class));
                }
                $resolved = [new $class($this->container), $method];
            }
        }

        if (! is_callable($resolved)) {
            throw new InvalidArgumentException(sprintf(
                '(%s) is not resolvable.',
                is_array($toResolve) || is_object($toResolve) || is_null($toResolve) ? json_encode($toResolve) : $toResolve
            ));
        }
        // TODO : vérifier l'utilité de ce bindTo !!!!!!! normalement ce n'est pas nécessaire car le container ajoute le $this lorsqu'on ajoute un service.
        // TODO : attention ce bout de code doit surement écraser le bind qu'on fait dans la fonction Router->map() quand on bindTo la closure avec $app. Ici on va surement défaire ce bindTo pour en refaire un avec $container
        if ($this->container instanceof ContainerInterface && $resolved instanceof Closure) {
            $resolved = $resolved->bindTo($this->container);
        }

        return $resolved;
    }



}
