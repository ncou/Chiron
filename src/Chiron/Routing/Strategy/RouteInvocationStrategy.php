<?php

declare(strict_types=1);

// https://github.com/symfony/http-kernel/blob/3.3/Tests/Controller/ControllerResolverTest.php

namespace Chiron\Routing\Strategy;

use Chiron\Routing\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Route callback strategy with route parameters as individual arguments.
 */
// TODO : à renommer en ApplicationStrategy
class RouteInvocationStrategy extends AbstractStrategy
{
    /** CallableResolverInterface */
    private $resolver;

    public function __construct(CallableResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    public function invokeRouteCallable(Route $route, ServerRequestInterface $request): ResponseInterface
    {
        $params = $route->getVars();
        // Inject individual matched parameters.
        foreach ($params as $param => $value) {
            $request = $request->withAttribute($param, $value);
        }

        $callable = $this->resolver->resolve($route->getHandler());
        $parameters = $this->bindParameters($request, $callable, $params);

        return $this->call($callable, $parameters);
    }
}
