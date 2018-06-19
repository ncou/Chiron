<?php

declare(strict_types=1);

namespace Chiron\Handler\Stack\Decorator;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

//use Zend\Stratigility\Exception;

/**
 * Decorate callable middleware as PSR-15 middleware.
 *
 * Decorates middleware with the following signature:
 *
 * <code>
 * function (
 *     ServerRequestInterface $request,
 *     RequestHandlerInterface $handler
 * ) : ResponseInterface
 * </code>
 *
 * such that it will operate as PSR-15 middleware.
 *
 * Neither the arguments nor the return value need be typehinted; however, if
 * the signature is incompatible, a PHP Error will likely be thrown.
 */
final class CallableRequestHandlerDecorator implements RequestHandlerInterface
{
    /**
     * @var callable
     */
    private $callable;

    public function __construct(callable $callable)
    {
        $this->callable = $callable;
    }

    /**
     * {@inheritdoc}
     *
     * @throws Exception\MissingResponseException if the decorated middleware
     *                                            fails to produce a response
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        //return ($this->middleware)($request, $callable);
        return call_user_func($this->callable, $request);
    }
}
