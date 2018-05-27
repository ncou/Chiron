<?php

namespace Chiron\Routing;

use Chiron\Routing\RoutableInterface;
use Chiron\Routing\VerbShortcutsTrait;
use Psr\Http\Server\RequestHandlerInterface;
use Chiron\Handler\Stack\RequestHandlerStack;
use Chiron\Handler\DeferredRequestHandler;
use Closure;

class RouteGroup implements RoutableInterface
{
    use VerbShortcutsTrait;

    protected $router;
    protected $container;
    protected $prefix;
    protected $middlewares = [];

    public function __construct($params, $router, $container)
    {
        $prefix = null;
        $middlewares = [];

        if (is_string($params)) {
            $prefix = $params;
        }

        if (is_array($params)) {
            $prefix = $params['prefix'] ?? null;
            $middlewares = $params['middleware'] ?? [];

            if (!is_array($middlewares)) {
                $middlewares = [$middlewares];
            }

            $this->middlewares += $middlewares;
        }

        //$this->prefix = trim($prefix, ' /');
        $this->prefix = $prefix;
        $this->router = $router;
        $this->container = $container;
    }

    public function map(string $pattern, $handler) : Route
    {
        //return $this->router->map($this->appendPrefixToUri($pattern), $handler, $this->middlewares);

        $handlerStack = $this->populateHandlerWithMiddlewares($handler, $this->middlewares);

        return $this->router->map($this->appendPrefixToUri($pattern), $handlerStack);
    }

    public function group($params, Closure $closure)//: RouteGroup
    {
        if (is_string($params)) {
            $params = $this->appendPrefixToUri($params);
        } elseif (is_array($params)) {
            $params['prefix'] = $params['prefix'] ? $this->appendPrefixToUri($params['prefix']) : null;
        }
        // TODO : il manque la gestion des middleware dans le cas de groupes imbriqués dans des groupes !!!!!!

        $group = new RouteGroup($params, $this->router);
        //$closure = $closure->bindTo($group);
        call_user_func($closure, $group);

        //return $this;
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
}
