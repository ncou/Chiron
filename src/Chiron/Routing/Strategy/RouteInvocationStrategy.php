<?php

declare(strict_types=1);

// https://github.com/symfony/http-kernel/blob/3.3/Tests/Controller/ControllerResolverTest.php

namespace Chiron\Routing\Strategy;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Chiron\Routing\Route;
use Chiron\Container\Container;

/**
 * Route callback strategy with route parameters as individual arguments.
 */
class RouteInvocationStrategy extends AbstractStrategy
{
    /** CallableResolverInterface */
    private $resolver;

    public function __construct(CallableResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    public function invokeRouteCallable(Route $route, ServerRequestInterface $request) : ResponseInterface
    {
        $callable = $this->resolver->resolve($route->getHandler());
        $parameters = $this->getParametersFromCallable($callable);
        $arguments = $this->bindAttributesWithParameters($parameters, $request);

        return call_user_func_array($callable, $arguments);
    }
}
