<?php

declare(strict_types=1);

namespace Chiron\Routing\Resolver;

use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * This class resolves a ControllerName of the format 'class@method' into a callable that can be invoked.
 */
final class ControllerResolver implements ControllerResolverInterface
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
     * Resolve toResolve into a callable that that the router can dispatch.
     *
     * If toResolve is of the format 'class@method', then try to extract 'class'
     * from the container otherwise instantiate it and then dispatch 'method'.
     *
     * @param callable|string $toResolve
     *
     * @throws \RuntimeException         if the callable does not exist
     * @throws \InvalidArgumentException if the callable is not resolvable
     *
     * @return callable
     */
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
            // check if the class is present un the container
            if ($this->container instanceof ContainerInterface && $this->container->has($class)) {
                $class = $this->container->get($class);
            } else {
                // if not present, try to instantitate it with the autoloader
                if (! class_exists($class)) {
                    throw new \RuntimeException(sprintf('Callable "%s" does not exist', $class));
                }
                // do not instantiate the classe when you use the magic method for generic static methods.
                if (! method_exists($class, '__callStatic')) {
                    $class = new $class();
                }
            }

            // For a class that implements RequestHandlerInterface, we will call the handle() method.
            if ($class instanceof RequestHandlerInterface) {
                $method = 'handle';
            }

            $resolved = [$class, $method];
        }

        if (! is_callable($resolved)) {
            throw new \InvalidArgumentException(sprintf(
                '(%s) is not resolvable.',
                is_array($toResolve) || is_object($toResolve) || is_null($toResolve) ? json_encode($toResolve) : $toResolve
            ));
        }

        return $resolved;
    }
}
