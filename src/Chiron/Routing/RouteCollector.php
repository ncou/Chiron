<?php

declare(strict_types=1);

namespace Chiron\Routing;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Container\ContainerInterface;

/**
 * Aggregate routes for the router.
 *
 * This class provides * methods for creating path+HTTP method-based routes and
 * injecting them into the router:
 *
 * - get
 * - post
 * - put
 * - patch
 * - delete
 * - any
 *
 * A general `route()` method allows specifying multiple request methods and/or
 * arbitrary request methods when creating a path-based route.
 *
 * Internally, the class performs some checks for duplicate routes when
 * attaching via one of the exposed methods, and will raise an exception when a
 * collision occurs.
 */
class RouteCollector implements RoutableInterface
{
    use RoutableTrait;
    // TODO : ajouter un ContainerAwareTrait

    /**
     * Dependency injection container.
     *
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var RouterInterface
     */
    //protected $router;
    /**
     * List of all routes registered directly with the application.
     *
     * @var Route[]
     */
    private $routes = [];

/*
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }
    */

    /**
     * Add a route for the route middleware to match.
     *
     * Accepts a combination of a path and middleware, and optionally the HTTP methods allowed.
     *
     * @param null|array $methods HTTP method to accept; null indicates any.
     * @param null|string $name The name of the route.
     * @throws Exception\DuplicateRouteException if specification represents an existing route.
     */
    // TODO : renommer $pattern en $path. + vérifier si le handler doit être typé. Normalement c'est un RequestHandlerInterface
    public function map(string $pattern, $handler): Route
    {
        // TODO : effectivement ajouter un check sur la duplication des routes.
        //$this->checkForDuplicateRoute($path, $methods);
        //$methods = null === $methods ? Route::HTTP_METHOD_ANY : $methods;
        //$route   = new Route($path, $middleware, $methods, $name);
        $route   = new Route($pattern, $handler);
        $this->routes[] = $route;
        //$this->router->addRoute($route);
        return $route;
    }

    /**
     * Add a group of routes to the collection.
     *
     * @param string $prefix
     * @param callable $group
     *
     * @return RouteGroup
     */
    // TODO : vérifier si le paramétre n'est pas plutot un objet Closure plutot qu'un callable !!!!
    public function group(string $prefix, callable $group): RouteGroup
    {
        /*
        $group = new RouteGroup($prefix, $group, $this);

        // process group: __invoke group object callable
        $group();

        return $group;*/

        $routeGroup = new RouteGroup($prefix, $this, $this->getContainer());
        // TODO : on fait un bind du this avec le group ????
        //$closure = $closure->bindTo($group);
        call_user_func($group, $routeGroup);

        // TODO : un return de type $group est à utiliser si on veux ajouter un middleware avec la notation : $app->group(xxxx, xxxxx)->middleware(xxx);
        return $routeGroup;
    }

    /**
     * Retrieve all directly registered routes with the application.
     *
     * @return Route[]
     */
    public function getRoutes() : array
    {
        return $this->routes;
    }
    /**
     * Determine if the route is duplicated in the current list.
     *
     * Checks if a route with the same name or path exists already in the list;
     * if so, and it responds to any of the $methods indicated, raises
     * a DuplicateRouteException indicating a duplicate route.
     *
     * @throws Exception\DuplicateRouteException on duplicate route detection.
     */
    private function checkForDuplicateRoute(string $path, array $methods = null) : void
    {
        if (null === $methods) {
            $methods = Route::HTTP_METHOD_ANY;
        }
        $matches = array_filter($this->routes, function (Route $route) use ($path, $methods) {
            if ($path !== $route->getPath()) {
                return false;
            }
            if ($methods === Route::HTTP_METHOD_ANY) {
                return true;
            }
            return array_reduce($methods, function ($carry, $method) use ($route) {
                return ($carry || $route->allowsMethod($method));
            }, false);
        });
        if (! empty($matches)) {
            $match = reset($matches);
            $allowedMethods = $match->getAllowedMethods() ?: ['(any)'];
            $name = $match->getName();
            throw new Exception\DuplicateRouteException(sprintf(
                'Duplicate route detected; path "%s" answering to methods [%s]%s',
                $match->getPath(),
                implode(',', $allowedMethods),
                $name ? sprintf(', with name "%s"', $name) : ''
            ));
        }
    }

    /**
     * Get container.
     *
     * @return null|ContainerInterface
     */
    public function getContainer(): ?ContainerInterface
    {
        return $this->container;
    }

    /**
     * Set container.
     *
     * @param ContainerInterface $container
     *
     * @return Application returns itself to support chaining
     */
    // TODO : voir si on conserve cette méthode ?????
    public function setContainer(ContainerInterface $container): self
    {
        $this->container = $container;

        return $this;
    }
}
