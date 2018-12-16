<?php

declare(strict_types=1);

namespace Chiron\Routing\Resolver;

use Closure;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;

/**
 * This class resolves a string of the format 'class@method' into a callable that can be invoked.
 */
final class CallableResolver implements CallableResolverInterface
{
    public const PATTERN = '~^([^@]+)@([^@]+)$~';
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface|null $container
     */
    // TODO : virer ce constructeur et faire étendre cette classe du Trait "ContainerAware" et "ContainerAwareInterface"
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
            if (preg_match(static::PATTERN, $toResolve, $matches)) {
                $class = $matches[1];
                $method = $matches[2];
            }
            // TODO : faire plutot un check de ce genre, car le container est initialisé en interne donc toujours une instanceof "ContainerInterface" : if ($this->container && $this->container->has($class)) {
            if ($this->container instanceof ContainerInterface && $this->container->has($class)) {
                $resolved = [$this->container->get($class), $method];
            } else {
                if (! class_exists($class)) {
                    throw new \RuntimeException(sprintf('Callable "%s" does not exist', $class));
                }
                $resolved = [new $class(), $method];
            }

            // For a class that implements RequestHandlerInterface, we will call handle()
            if ($resolved[0] instanceof RequestHandlerInterface) {
                $resolved[1] = 'handle';
            }
        }

        if (! is_callable($resolved)) {
            throw new InvalidArgumentException(sprintf(
                '(%s) is not resolvable.',
                is_array($toResolve) || is_object($toResolve) || is_null($toResolve) ? json_encode($toResolve) : $toResolve
            ));
        }

        return $resolved;
    }
}
