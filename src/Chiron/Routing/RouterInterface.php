<?php

declare(strict_types=1);

namespace Chiron\Routing;

use Psr\Http\Message\ServerRequestInterface;

interface RouterInterface
{
    /**
     * Group a bunch of routes.
     *
     * @param string   $prefix
     * @param callable $group
     *
     * @return \Chiron\Routing\RouteGroup
     */
    public function group(string $prefix, callable $group): RouteGroup;

    /**
     * Add a route to the map.
     *
     * @param string          $path
     * @param callable|string $handler
     *
     * @return \Chiron\Routing\Route
     */
    public function map(string $path, $handler): Route;


    public function match(ServerRequestInterface $request): RouteResult;

    /**
     * Set the base path.
     * Useful if you are running your application from a subdirectory.
     */
    public function setBasePath(string $basePath): void;

    /**
     * Get the router base path.
     * Useful if you are running your application from a subdirectory.
     */
    public function getBasePath(): string;

    // TODO : ajouter les méthodes : generateUri / getRoutes   => attention pas la peine de mettre la méthode addRoute car c'est géré via map() pour ajouter une route.
    // TODO : réflaichir si on doit ajouter les méthodes : getNamedRoute/removeNamedRoute dans cette interface.
}
