<?php

namespace Chiron\Routing;

use Closure;

interface RoutableInterface
{
    public function map(string $pattern, $handler): Route;

    public function get(string $pattern, $handler): Route;

    public function post(string $pattern, $handler): Route;

    public function patch(string $pattern, $handler): Route;

    public function put(string $pattern, $handler): Route;

    public function delete(string $pattern, $handler): Route;

    public function options(string $pattern, $handler): Route;

    public function group(string $prefix, Closure $closure): RouteGroup;
}
