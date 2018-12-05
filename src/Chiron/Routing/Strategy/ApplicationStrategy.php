<?php

declare(strict_types=1);

// https://github.com/symfony/http-kernel/blob/3.3/Tests/Controller/ControllerResolverTest.php

namespace Chiron\Routing\Strategy;

use Chiron\Http\Psr\Response;
use Chiron\Routing\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use InvalidArgumentException;
use LogicException;

/**
 * Route callback strategy with route parameters as individual arguments.
 */
class ApplicationStrategy extends AbstractStrategy
{
    /** CallableResolverInterface */
    private $resolver;
    /** ResponseFactoryInterface */
    private $responseFactory;

    public function __construct(ResponseFactoryInterface $responseFactory, CallableResolverInterface $resolver)
    {
        $this->resolver = $resolver;
        $this->responseFactory = $responseFactory;
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

        $content = $this->call($callable, $parameters);

        if (! $content instanceof ResponseInterface) {
            if (! is_string($content)){
                throw new LogicException('Your controller should return a string or a ResponseInterface instance.');
            }

            // TODO : créer une méthode createResponse dans la classe abstraite avec comme signature : create($content = null, $status = 200, array $headers = [])
            $response = $this->responseFactory->createResponse(200);
            $response = $response->withHeader('Content-Type', 'text/html');
            $response->getBody()->write($content);
            return $response;
        }

        return $content;
    }
}
