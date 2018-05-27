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
        ContainerInterface $container,
        string $middlewareName
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
        $middleware = $this->container->get($this->middlewareName);

        // Try to inject the dependency injection container in the middleware
        /*
        if (is_callable([$middleware, 'setContainer']) && $this->container instanceof ContainerInterface) {
            $middleware->setContainer($this->container);
        }*/

        return $middleware->process($request, $handler);
    }
}
