<?php

namespace Chiron\Routing;

use Closure;
use Psr\Container\ContainerInterface;

class RouteGroup implements RoutableInterface
{
    use RoutableTrait;

    protected $router;

    protected $container;

    protected $prefix;

    protected $middlewares = [];

    public function __construct(string $prefix, Router $router, ContainerInterface $container = null)
    {
        //$this->prefix = trim($prefix, ' /');
        $this->prefix = $prefix;
        $this->router = $router;
        $this->container = $container;
    }

    public function map(string $pattern, $handler): Route
    {
        //return $this->router->map($this->appendPrefixToUri($pattern), $handler, $this->middlewares);
        $route = $this->router->map($this->appendPrefixToUri($pattern), $handler);

        // store the group un the extra section on the route. Used later to get the middleware attached to the group and apply them on the route.
        return $route->addExtra(RouteGroup::class, $this);
    }

    // TODO : vérifier l'utilité de faire des group de group...
    public function group(string $prefix, Closure $closure): self
    {
        $group = new self($prefix, $this->router);
        //$closure = $closure->bindTo($group);
        call_user_func($closure, $group);

        //return $this;
        return $group;
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
}
