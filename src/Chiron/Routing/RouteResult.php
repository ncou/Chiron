<?php

declare(strict_types=1);

namespace Chiron\Routing;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

// TODO : regarder ici : https://github.com/l0gicgate/Slim/blob/4.x-DispatcherResults/Slim/DispatcherResults.php
//https://github.com/slimphp/Slim/blob/4.x/Slim/RoutingResults.php

/**
 * Value object representing the results of routing.
 *
 * RouterInterface::match() is defined as returning a RouteResult instance,
 * which will contain the following state:
 *
 * - isSuccess()/isFailure() indicate whether routing succeeded or not.
 * - On success, it will contain:
 *   - the matched route name (typically the path)
 *   - the matched route middleware
 *   - any parameters matched by routing
 * - On failure:
 *   - isMethodFailure() further qualifies a routing failure to indicate that it
 *     was due to using an HTTP method not allowed for the given path.
 *   - If the failure was due to HTTP method negotiation, it will contain the
 *     list of allowed HTTP methods.
 *
 * RouteResult instances are consumed by the Application in the routing
 * middleware.
 */
// TODO : renommer la classe en RoutingResults ????
class RouteResult implements RequestHandlerInterface
{
    // TODO : voir si on déplace cette constante dans la classe "Route"
    public const HTTP_METHOD_ANY = null;

    /**
     * @var null|string[]
     */
    private $allowedMethods = [];

    /**
     * @var array
     */
    private $matchedParams = [];

    /**
     * @var string
     */
    private $matchedRouteName;

    /**
     * @var array
     */
    private $matchedRouteMiddlewareStack;

    /**
     * Route matched during routing.
     *
     * @var Route
     */
    private $route;

    /**
     * @var bool success state of routing
     */
    private $success;

    /**
     * Only allow instantiation via factory methods (fromRoute or fromRouteFailure).
     */
    //TODO : à virer !!! et créer un vrai constructeur !!!!
    private function __construct()
    {
    }

    /**
     * Create an instance representing a route succes from the matching route.
     *
     * @param array $params parameters associated with the matched route, if any
     */
    public static function fromRoute(Route $route, array $params = []): self
    {
        $result = new self();
        $result->success = true;
        $result->route = $route;
        $result->matchedParams = $params;

        return $result;
    }

    /**
     * Create an instance representing a route failure.
     *
     * @param null|array $methods HTTP methods allowed for the current URI, if any.
     *                            null is equivalent to allowing any HTTP method; empty array means none.
     */
    public static function fromRouteFailure(?array $methods): self
    {
        $result = new self();
        $result->success = false;
        $result->allowedMethods = $methods;

        return $result;
    }

    /**
     * Does the result represent successful routing?
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * Is this a routing failure result?
     */
    public function isFailure(): bool
    {
        return ! $this->success;
    }

    /**
     * Does the result represent failure to route due to HTTP method?
     */
    public function isMethodFailure(): bool
    {
        if ($this->isSuccess() || $this->allowedMethods === self::HTTP_METHOD_ANY) {
            return false;
        }

        return true;
    }

    /**
     * Retrieve the route that resulted in the route match.
     *
     * @return false|null|Route false if representing a routing failure;
     *                          null if not created via fromRoute(); Route instance otherwise
     */
    public function getMatchedRoute()
    {
        return $this->isFailure() ? false : $this->route;
    }

    /**
     * Retrieve the matched route name, if possible.
     *
     * If this result represents a failure, return false; otherwise, return the
     * matched route name.
     *
     * @return false|string
     */
    // TODO : méthode à virer elle ne sert à rien !!!!
    public function getMatchedRouteName()
    {
        if ($this->isFailure()) {
            return false;
        }
        if (! $this->matchedRouteName && $this->route) {
            $this->matchedRouteName = $this->route->getName();
        }

        return $this->matchedRouteName;
    }

    /**
     * Retrieve all the middlewares, if possible.
     *
     * If this result represents a failure, return false; otherwise, return the
     * middleware of the Route + middleware of the RouteGroup.
     *
     * @return false|array
     */
    public function getMatchedRouteMiddlewareStack()
    {
        if ($this->isFailure()) {
            return false;
        }

        if (! $this->matchedRouteMiddlewareStack && $this->route) {
            $this->matchedRouteMiddlewareStack = $this->route->gatherMiddlewareStack();
        }

        return $this->matchedRouteMiddlewareStack;
    }

    /**
     * Returns the matched params.
     *
     * Guaranted to return an array, even if it is simply empty.
     */
    // TODO : faire un rawurldecode sur les paramétres ???? => https://github.com/slimphp/Slim/blob/4.x/Slim/RoutingResults.php#L121
    public function getMatchedParams(): array
    {
        return $this->matchedParams;
    }

    /**
     * Retrieve the allowed methods for the route failure.
     *
     * @return null|string[] HTTP methods allowed
     */
    public function getAllowedMethods(): ?array
    {
        if ($this->isSuccess()) {
            return $this->route
                ? $this->route->getAllowedMethods()
                : [];
        }

        return $this->allowedMethods;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Merge the default values defined in the Route with the parameters, and add the request class name used to resole the callable parameters using type hint.
        $params = array_merge($this->route->getDefaults(), $this->matchedParams, [ServerRequestInterface::class => $request]);

        return $this->route->getStrategy()->invokeRouteHandler($this->route->getHandler(), $params, $request);
    }
}
