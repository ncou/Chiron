<?php

declare(strict_types=1);

namespace Chiron\Routing;

use FastRoute\Dispatcher\GroupCountBased as GroupCountBasedDispatcher;
use Psr\Http\Message\ServerRequestInterface;

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
     * Dispatch the current route.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function dispatchRequest(ServerRequestInterface $request): RouteResult
    {
        $method = $request->getMethod();
        $path = $request->getUri()->getPath(); //TODO vérifier pourquoi certain font un rawurldecode exemple dans Slim :    $uri = '/' . ltrim(rawurldecode($request->getUri()->getPath()), '/');

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

        $params = array_map('urldecode', $params);

        return RouteResult::fromRoute($route, $params);
    }
}
