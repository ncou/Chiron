<?php

namespace Chiron\Routing;

use Closure;

interface RoutableInterface
{
    public function map(string $pattern, $handler, $middlewares = null): Route;

    public function get(string $pattern, $handler, $middlewares = null): Route;

    public function post(string $pattern, $handler, $middlewares = null): Route;

    public function patch(string $pattern, $handler, $middlewares = null): Route;

    public function put(string $pattern, $handler, $middlewares = null): Route;

    public function delete(string $pattern, $handler, $middlewares = null): Route;

    public function options(string $pattern, $handler, $middlewares = null): Route;

    public function group($params, Closure $closure): RouteGroup;
}
