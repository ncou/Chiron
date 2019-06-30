<?php

declare(strict_types=1);

namespace Chiron\Routing;

use Chiron\Routing\Traits\MiddlewareAwareInterface;
use Chiron\Routing\Traits\StrategyAwareInterface;
use Psr\Http\Message\ServerRequestInterface;

interface RouterInterface extends MiddlewareAwareInterface, StrategyAwareInterface
{

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

    public function getRouteCollector(): RouteCollectorInterface;

    public function setRouteCollector(RouteCollectorInterface $collector): void;

    public function urlFor(string $routeName, array $substitutions = [], array $queryParams = []): string;

    public function relativeUrlFor(string $routeName, array $substitutions = [], array $queryParams = []): string;

    // TODO : ajouter les méthodes : generateUri / getRoutes   => attention pas la peine de mettre la méthode addRoute car c'est géré via map() pour ajouter une route.
    // TODO : réflaichir si on doit ajouter les méthodes : getNamedRoute/removeNamedRoute dans cette interface.
}
