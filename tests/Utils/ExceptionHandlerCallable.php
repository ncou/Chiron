<?php

declare(strict_types=1);

namespace Chiron\Tests\Utils;

use Chiron\Handler\Error\ExceptionHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

class ExceptionHandlerCallable implements ExceptionHandlerInterface
{
    /**
     * @var callable
     */
    private $adaptee;

    /**
     * @param RequestHandlerInterface $adaptee
     */
    public function __construct(callable $adaptee)
    {
        $this->adaptee = $adaptee;
    }

    /**
     * Process the request using a handler.
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function handleException(Throwable $exception, ServerRequestInterface $request): ResponseInterface
    {
        return call_user_func_array($this->adaptee, [$exception, $request]);
    }
}
