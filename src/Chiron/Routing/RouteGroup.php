<?php

namespace Chiron\Routing;

use Chiron\Handler\DeferredRequestHandler;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RouteGroup implements RoutableInterface
{
    use RoutableTrait;

    /** @var RouteCollectionInterface */
    private $collector;



    private $router;

    private $container;

    private $prefix;

    private $middlewares = [];

    public function __construct(string $prefix, $collector, ContainerInterface $container = null)
    {
        $this->prefix = $prefix;
        $this->collector = $collector;
        $this->container = $container;
    }

    /**
     * map the route.
     *
     * @param string                                  $pattern The route URI pattern
     * @param RequestHandlerInterface|callable|string $handler The route callback routine
     *
     * @return \Chiron\Routing\Route
     */
    public function map(string $pattern, $handler): Route
    {
        if (is_string($handler) || is_callable($handler)) {
            $handler = new DeferredRequestHandler($handler, $this->container);
        }

        if (! $handler instanceof RequestHandlerInterface) {
            throw new InvalidArgumentException('Handler should be a Psr\Http\Server\RequestHandlerInterface instance');
        }

        $route = $this->collector->map($this->appendPrefixToUri($pattern), $handler);

        // store the group in the "extra" section on the route object. Used later to get the middleware attached to the group and apply them on the route.
        //return $route->addExtra(RouteGroup::class, $this);

        $route->setParentGroup($this);

        return $route;
    }

    private function appendPrefixToUri(string $uri)
    {
        return $this->prefix . $uri;
    }

    /**
     * Get the middlewares registered for the group.
     *
     * @return mixed[]
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    /**
     * Prepend middleware to the middleware collection.
     *
     * @param mixed $middleware The callback routine
     *
     * @return static
     */
    // TODO : gérer la possibilité de passer un tableau de middleware, attention aux tableaux de tableaux de tableaux....
    public function middleware($middleware): self
    {
        $this->middlewares[] = $middleware;

        return $this;
    }

    /**
     * Process the group and ensure routes are added to the collection.
     */
    /*
    public function __invoke()
    {
        call_user_func_array($this->callback->bindTo($this), [$this]);
    }*/
}
