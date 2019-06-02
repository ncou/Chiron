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
class HtmlStrategy implements StrategyInterface
{
    /** KernelInterface */
    private $kernel;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    // $handler => string ou callable
    // TODO : virer l'argument $request on utilisera celui qui se trouve dans le tableau $params !!!!
    public function invokeRouteHandler($handler, array $params, ServerRequestInterface $request): ResponseInterface
    {
        // Inject individual matched parameters.
        foreach ($params as $param => $value) {
            $request = $request->withAttribute($param, $value);
        }
        $params[ServerRequestInterface::class] = $request;

        $result = $this->kernel->call($handler, $params);

        if (! $result instanceof ResponseInterface) {
            // TODO : gérer le cas ou l'objet a une méthode toString() =>    if (is_object($result) && method_exists($result, '__toString')              https://github.com/middlewares/utils/blob/master/src/CallableHandler.php#L74
            // TODO : mieux gérer les buffer avant de retourner la chaine de caractére => https://github.com/middlewares/utils/blob/master/src/CallableHandler.php#L61
            if (! is_string($result)) {
                throw new LogicException('Your controller should return a string or a ResponseInterface instance.');
            }

            return $this->kernel->createResponse($result, 200, ['Content-Type' => 'text/html']);
        }

        return $result;
    }
}
