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

        if (! $middleware instanceof MiddlewareInterface) {
            throw new \InvalidArgumentException('The middleware present in the container should be a Psr\Http\Server\MiddlewareInterface instance');
        }

        // Try to inject the dependency injection container in the middleware
        /*
        if (is_callable([$middleware, 'setContainer']) && $this->container instanceof ContainerInterface) {
            $middleware->setContainer($this->container);
        }*/

        return $middleware->process($request, $handler);
    }
}
