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
     * Support a instance of MiddlewareInterface or a callable.
     *
     * @throws InvalidArgumentException for invalid middleware types pulled from the container
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // retrieve the middleware in the container. It could be a : MiddlewareInterface object, or a callable
        $entry = $this->container->get($this->middlewareName);

        if (is_callable($entry)) {
            return call_user_func_array($entry, [$request, $handler]);
        }

        if ($entry instanceof MiddlewareInterface) {
            return $entry->process($request, $handler);
        }

        // Try to inject the dependency injection container in the middleware
        /*
        if (is_callable([$middleware, 'setContainer']) && $this->container instanceof ContainerInterface) {
            $middleware->setContainer($this->container);
        }*/

        throw new \InvalidArgumentException('The middleware present in the container should be a PHP callable or a Psr\Http\Server\MiddlewareInterface instance');
    }
}
