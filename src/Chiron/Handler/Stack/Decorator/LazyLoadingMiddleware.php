<?php
/**
 * @see       https://github.com/zendframework/zend-expressive for the canonical source repository
 *
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Chiron\Handler\Stack\Decorator;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LazyLoadingMiddleware implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var string
     */
    private $middlewareName;

    public function __construct(
        string $middlewareName,
        ContainerInterface $container
    ) {
        $this->container = $container;
        $this->middlewareName = $middlewareName;
    }

    /**
     * @throws InvalidMiddlewareException for invalid middleware types pulled
     *                                    from the container
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // retrieve the middleware in the container. It could be a : MiddlewareInterface object, or a callable
        $middleware = $this->container->get($this->middlewareName);

        // TODO : lever une exception \InvalidArgumentException si le type de l'objet récupéré dans le container n'est pas un callable ou un MiddlewareInterface !!!!!

        if (is_callable($middleware)) {
            // TODO : faire plutot un fonction execute avec en paramétre la request et le handler, car sinon il faut faire une liaison avec la classe CallableMiddlewareDecorator !!!!!
            $middleware = new CallableMiddlewareDecorator($middleware);
        }

        // Try to inject the dependency injection container in the middleware
        /*
        if (is_callable([$middleware, 'setContainer']) && $this->container instanceof ContainerInterface) {
            $middleware->setContainer($this->container);
        }*/

        return $middleware->process($request, $handler);
    }
}
