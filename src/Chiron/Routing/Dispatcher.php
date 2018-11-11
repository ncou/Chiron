<?php

declare(strict_types=1);

namespace Chiron\Routing;

use Chiron\MiddlewareAwareInterface;
use Chiron\MiddlewareAwareTrait;
use Chiron\Routing\Strategy\StrategyAwareInterface;
use Chiron\Routing\Strategy\StrategyAwareTrait;
use Chiron\Routing\Strategy\StrategyInterface;
use FastRoute\DataGenerator;
use FastRoute\Dispatcher as FastRoute;
use FastRoute\Dispatcher\GroupCountBased as GroupCountBasedDispatcher;
use FastRoute\RouteParser;
use InvalidArgumentException;
use RuntimeException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

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
class Dispatcher extends GroupCountBasedDispatcher
{
    private $routes;

    // mixed[] $data
    public function __construct(array $routes, array $data)
    {
        $this->routes = $routes;
        parent::__construct($data);
    }

    /**
     * Dispatch the current route
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function dispatchRequest(ServerRequestInterface $request) : RouteResult
    {
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();

        $result = $this->dispatch($method, $path);

        return $result[0] !== self::FOUND
            ? $this->marshalFailedRoute($result)
            : $this->marshalMatchedRoute($result, $method);
    }


    /**
     * Marshal a routing failure result.
     *
     * If the failure was due to the HTTP method, passes the allowed HTTP
     * methods to the factory.
     */
    private function marshalFailedRoute(array $result): RouteResult
    {
        if ($result[0] === self::METHOD_NOT_ALLOWED) {
            return RouteResult::fromRouteFailure($result[1]);
        }

        return RouteResult::fromRouteFailure(RouteResult::HTTP_METHOD_ANY);
    }

    /**
     * Marshals a route result based on the results of matching and the current HTTP method.
     */
    private function marshalMatchedRoute(array $result, string $method): RouteResult
    {
        $identifier = $result[1];
        $route = $this->routes[$identifier];
        $params = $result[2];

        /*
        $options = $route->getOptions();
        if (! empty($options['defaults'])) {
            $params = array_merge($options['defaults'], $params);
        }*/

        return RouteResult::fromRoute($route, $params);
    }
}
