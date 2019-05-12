<?php

declare(strict_types=1);

// https://github.com/symfony/http-kernel/blob/3.3/Tests/Controller/ControllerResolverTest.php

namespace Chiron\Routing\Strategy;

use Chiron\Routing\Resolver\ControllerResolverInterface;
use Chiron\Routing\Route;
use Chiron\KernelInterface;
use LogicException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Route callback strategy with route parameters as individual arguments.
 */
// TODO : classe à renommer en HtmlStrategy
class ApplicationStrategy implements StrategyInterface
{
    /** KernelInterface */
    private $kernel;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    // $handler => string ou callable
    public function invokeRouteHandler($handler, array $params, ServerRequestInterface $request): ResponseInterface
    {
        // Inject individual matched parameters.
        foreach ($params as $param => $value) {
            $request = $request->withAttribute($param, $value);
        }

        $result = $this->kernel->call($handler, $params);

        if (! $result instanceof ResponseInterface) {
            if (! is_string($result)) {
                throw new LogicException('Your controller should return a string or a ResponseInterface instance.');
            }

            return $this->kernel->createResponse($result, 200, ['Content-Type' => 'text/html']);
        }

        return $result;
    }
}
