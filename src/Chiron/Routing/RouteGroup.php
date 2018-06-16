<?php

namespace Chiron\Routing;

use Chiron\Handler\DeferredRequestHandler;
use Chiron\Handler\Stack\RequestHandlerStack;
use Closure;
use Psr\Container\ContainerInterface;

class RouteGroup implements RoutableInterface
{
    use RoutableTrait;

    protected $router;

    protected $container;

    protected $prefix;

    protected $middlewares = [];

    public function __construct($params, Router $router, ContainerInterface $container = null)
    {
        $prefix = null;
        $middlewares = [];

        if (is_string($params)) {
            $prefix = $params;
        }

        if (is_array($params)) {
            $prefix = $params['prefix'] ?? null;
            $middlewares = $params['middleware'] ?? [];

            if (! is_array($middlewares)) {
                $middlewares = [$middlewares];
            }

            $this->middlewares += $middlewares;
        }

        //$this->prefix = trim($prefix, ' /');
        $this->prefix = $prefix;
        $this->router = $router;
        $this->container = $container;
    }

    public function map(string $pattern, $handler, $middlewares = null): Route
    {
        //return $this->router->map($this->appendPrefixToUri($pattern), $handler, $this->middlewares);

        if (! isset($middlewares)) {
            $middlewares = [];
        } elseif (! is_array($middlewares)) {
            $middlewares = [$middlewares];
        }

        $middlewares = array_merge($this->middlewares, $middlewares);

        $handlerStack = $this->populateHandlerWithMiddlewares($handler, $middlewares);

        $route = $this->router->map($this->appendPrefixToUri($pattern), $handlerStack);

        // store the group un the extra section on the route. Used later to get the middleware attached to the group and apply them on the route.
        $route->addExtra(RouteGroup::class, $this);

        return $route;
    }

    // TODO : vérifier l'utilité de faire des group de group...
    public function group($params, Closure $closure): self
    {
        if (is_string($params)) {
            $params = $this->appendPrefixToUri($params);
        } elseif (is_array($params)) {
            $params['prefix'] = $params['prefix'] ? $this->appendPrefixToUri($params['prefix']) : null;
        }

        $group = new self($params, $this->router);
        //$closure = $closure->bindTo($group);
        call_user_func($closure, $group);

        return $this;
    }

    private function appendPrefixToUri(string $uri)
    {
        return $this->prefix . $uri;
    }

    private function populateHandlerWithMiddlewares($handler, array $middlewares)
    {
        if (is_string($handler) || is_callable($handler)) {
            $handler = new DeferredRequestHandler($handler, $this->container);
        }

        if (empty($middlewares)) {
            return $handler;
        }

        $handlerStack = new RequestHandlerStack($handler);
        foreach ($middlewares as $middleware) {
            $handlerStack->prepend($this->prepareMiddleware($middleware));
        }

        return $handlerStack;
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
